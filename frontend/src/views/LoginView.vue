<template>
  <div class="login-root">
    <div class="blob blob-1"/>
    <div class="blob blob-2"/>
    <div class="blob blob-3"/>

    <div class="login-card">
      <div class="logo-wrap">
        <div class="logo-ring">
          <v-icon size="28" color="white">mdi-home-city</v-icon>
        </div>
      </div>

      <div class="login-heading">
        <h1>YCA Hub</h1>
        <p>Your Community, Amplified</p>
      </div>

      <transition name="shake">
        <div v-if="error" class="error-pill">
          <v-icon size="14" color="white" class="mr-1">mdi-alert-circle</v-icon>
          {{ error }}
        </div>
      </transition>

      <div class="form-group">
        <label class="field-label">Email address</label>
        <div class="field-wrap" :class="{ focused: focused === 'email' }">
          <v-icon size="16" class="field-icon">mdi-email-outline</v-icon>
          <input
            v-model="form.email"
            type="email"
            placeholder="you@example.com"
            autocomplete="email"
            @focus="focused = 'email'"
            @blur="focused = null"
            @keydown.enter="focusPassword"
            ref="emailRef"
          />
        </div>
      </div>

      <div class="form-group">
        <label class="field-label">Password</label>
        <div class="field-wrap" :class="{ focused: focused === 'password' }">
          <v-icon size="16" class="field-icon">mdi-lock-outline</v-icon>
          <input
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            placeholder="••••••••"
            autocomplete="current-password"
            @focus="focused = 'password'"
            @blur="focused = null"
            @keydown.enter="handleLogin"
            ref="passwordRef"
          />
          <button class="eye-btn" @click="showPassword = !showPassword" type="button">
            <v-icon size="15">{{ showPassword ? 'mdi-eye-off-outline' : 'mdi-eye-outline' }}</v-icon>
          </button>
        </div>
      </div>

      <button class="signin-btn" :class="{ loading }" :disabled="loading" @click="handleLogin">
        <span v-if="!loading">Sign In</span>
        <span v-else class="btn-spinner"/>
      </button>

      <div class="divider"><span>or</span></div>

      <div class="register-row">
        <span>Don't have an account?</span>
        <router-link to="/register">Create account</router-link>
      </div>
    </div>

    <div class="login-footer">
      Developer — Justine Philip Villarosa © {{ new Date().getFullYear() }}
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router       = useRouter()
const auth         = useAuthStore()
const loading      = ref(false)
const error        = ref(null)
const focused      = ref(null)
const showPassword = ref(false)
const emailRef     = ref(null)
const passwordRef  = ref(null)
const form         = ref({ email: '', password: '' })

function focusPassword() { passwordRef.value?.focus() }

async function handleLogin() {
  if (!form.value.email || !form.value.password) {
    error.value = 'Please enter your email and password.'
    return
  }
  loading.value = true
  error.value   = null
  try {
    await auth.login(form.value.email, form.value.password)
    router.push('/dashboard')
  } catch (e) {
    error.value = e.response?.data?.message || 'Invalid credentials. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

.login-root {
  min-height: 100dvh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: #0a0f1e;
  position: relative;
  overflow: hidden;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  padding: 24px 16px;
}

.blob {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.35;
  animation: drift 12s ease-in-out infinite alternate;
}
.blob-1 { width: 500px; height: 500px; background: radial-gradient(circle, #1a3a8f, #0d2260); top: -120px; left: -100px; }
.blob-2 { width: 400px; height: 400px; background: radial-gradient(circle, #0d5c8f, #063a5c); bottom: -80px; right: -80px; animation-delay: -4s; }
.blob-3 { width: 300px; height: 300px; background: radial-gradient(circle, #1a237e, #0a1550); top: 50%; left: 50%; animation-delay: -8s; }
@keyframes drift {
  from { transform: translate(0, 0) scale(1); }
  to   { transform: translate(30px, -20px) scale(1.08); }
}
.blob-3 { animation-name: drift3; }
@keyframes drift3 {
  from { transform: translate(-50%, -50%) scale(1); }
  to   { transform: translate(calc(-50% + 20px), calc(-50% + 15px)) scale(1.05); }
}

.login-card {
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 400px;
  background: rgba(255,255,255,0.06);
  backdrop-filter: blur(40px) saturate(180%);
  -webkit-backdrop-filter: blur(40px) saturate(180%);
  border: 1px solid rgba(255,255,255,0.12);
  border-radius: 20px;
  padding: 36px 32px;
  box-shadow: 0 32px 64px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,255,255,0.05) inset, 0 1px 0 rgba(255,255,255,0.1) inset;
}

.logo-wrap { display: flex; justify-content: center; margin-bottom: 20px; }
.logo-ring {
  width: 56px; height: 56px; border-radius: 16px;
  background: linear-gradient(135deg, #1a3a8f, #0d5c8f);
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 8px 24px rgba(13,92,143,0.5), 0 0 0 1px rgba(255,255,255,0.15) inset;
}

.login-heading { text-align: center; margin-bottom: 24px; }
.login-heading h1 {
  font-size: 22px; font-weight: 700;
  color: rgba(255,255,255,0.95);
  letter-spacing: -0.5px; margin: 0 0 4px;
}
.login-heading p { font-size: 12px; color: rgba(255,255,255,0.4); margin: 0; letter-spacing: 0.5px; }

.error-pill {
  display: flex; align-items: center;
  background: rgba(239,68,68,0.15);
  border: 1px solid rgba(239,68,68,0.3);
  color: #fca5a5; font-size: 12px;
  padding: 8px 12px; border-radius: 10px; margin-bottom: 16px;
}

.form-group { margin-bottom: 14px; }
.field-label { display: block; font-size: 12px; font-weight: 500; color: rgba(255,255,255,0.55); margin-bottom: 6px; }
.field-wrap {
  display: flex; align-items: center;
  background: rgba(255,255,255,0.07);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 12px; padding: 0 14px; height: 46px;
  transition: all 0.2s ease; gap: 10px;
}
.field-wrap.focused { background: rgba(255,255,255,0.1); border-color: rgba(99,155,255,0.6); box-shadow: 0 0 0 3px rgba(99,155,255,0.12); }
.field-icon { color: rgba(255,255,255,0.35) !important; flex-shrink: 0; }
.field-wrap.focused .field-icon { color: rgba(99,155,255,0.8) !important; }
.field-wrap input { flex: 1; background: none; border: none; outline: none; color: rgba(255,255,255,0.9); font-size: 14px; font-family: inherit; }
.field-wrap input::placeholder { color: rgba(255,255,255,0.2); }
.eye-btn { background: none; border: none; cursor: pointer; color: rgba(255,255,255,0.3); display: flex; align-items: center; padding: 0; transition: color 0.2s; }
.eye-btn:hover { color: rgba(255,255,255,0.65); }

.signin-btn {
  width: 100%; height: 46px; margin-top: 20px;
  border: none; border-radius: 12px;
  background: linear-gradient(135deg, #1a4fd4, #0d8fd4);
  color: white; font-size: 14px; font-weight: 600;
  font-family: inherit; cursor: pointer;
  transition: all 0.2s ease;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 16px rgba(26,79,212,0.4);
  position: relative; overflow: hidden;
}
.signin-btn::before { content: ''; position: absolute; inset: 0; background: rgba(255,255,255,0.08); border-radius: inherit; opacity: 0; transition: opacity 0.2s; }
.signin-btn:hover::before { opacity: 1; }
.signin-btn:active { transform: scale(0.98); }
.signin-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-spinner { width: 18px; height: 18px; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.divider { display: flex; align-items: center; gap: 12px; margin: 20px 0 16px; }
.divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: rgba(255,255,255,0.08); }
.divider span { font-size: 11px; color: rgba(255,255,255,0.25); }

.register-row { text-align: center; font-size: 13px; color: rgba(255,255,255,0.4); }
.register-row a { color: rgba(99,155,255,0.9); text-decoration: none; font-weight: 500; margin-left: 5px; transition: color 0.2s; }
.register-row a:hover { color: #fff; }

.login-footer { position: relative; z-index: 10; margin-top: 24px; font-size: 11px; color: rgba(255,255,255,0.18); letter-spacing: 0.3px; }

.shake-enter-active { animation: shake 0.4s ease; }
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  20% { transform: translateX(-6px); }
  40% { transform: translateX(6px); }
  60% { transform: translateX(-4px); }
  80% { transform: translateX(4px); }
}

@media (max-width: 480px) {
  .login-card { padding: 28px 22px; border-radius: 18px; }
  .login-heading h1 { font-size: 18px; }
}
</style>
