<template>
  <v-container class="d-flex align-center justify-center" style="min-height:100vh">
    <v-card width="420" elevation="4" rounded="lg">
      <v-card-title class="text-center pa-6">
        <v-icon size="48" color="primary">mdi-home-city</v-icon>
        <div class="text-h5 font-weight-bold mt-2">Barangay Smart Connect</div>
        <div class="text-body-2 text-medium-emphasis">Sign in to your account</div>
      </v-card-title>

      <v-card-text class="px-6 pb-6">
        <v-alert v-if="error" type="error" class="mb-4" density="compact">
          {{ error }}
        </v-alert>

        <v-text-field
          v-model="form.email"
          label="Email address"
          type="email"
          prepend-inner-icon="mdi-email-outline"
          variant="outlined"
          class="mb-3"
        />

        <v-text-field
          v-model="form.password"
          label="Password"
          type="password"
          prepend-inner-icon="mdi-lock-outline"
          variant="outlined"
          class="mb-4"
        />

        <v-btn
          block
          color="primary"
          size="large"
          :loading="loading"
          @click="handleLogin"
        >
          Sign In
        </v-btn>

        <div class="text-center mt-4 text-body-2">
          Don't have an account?
          <router-link to="/register" class="text-primary">Register</router-link>
        </div>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router  = useRouter()
const auth    = useAuthStore()
const loading = ref(false)
const error   = ref(null)

const form = ref({ email: '', password: '' })

async function handleLogin() {
  loading.value = true
  error.value   = null
  try {
    await auth.login(form.value.email, form.value.password)
    router.push('/dashboard')
  } catch (e) {
    error.value = e.response?.data?.message || 'Login failed. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>