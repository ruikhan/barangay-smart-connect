<template>
  <v-container
    fluid
    class="register-bg d-flex align-center justify-center"
    style="min-height:100vh"
  >
    <v-row justify="center" class="w-100 ma-0">
      <v-col cols="12" sm="10" md="8" lg="5" xl="4">

        <!-- Logo header -->
        <div class="text-center mb-6">
          <v-avatar size="64" color="primary" rounded="xl" class="mb-3 elevation-4">
            <v-icon size="36" color="white">mdi-home-city</v-icon>
          </v-avatar>
          <div class="text-h5 font-weight-bold text-primary">Barangay Smart Connect</div>
          <div class="text-body-2 text-medium-emphasis mt-1">
            Create your resident account
          </div>
        </div>

        <v-card rounded="xl" elevation="0" border class="pa-2 pa-sm-4">

          <!-- Step indicator -->
          <div class="d-flex align-center justify-center gap-2 pt-4 pb-2">
            <div
              v-for="s in 3"
              :key="s"
              class="step-dot"
              :class="s === step ? 'step-dot--active' : s < step ? 'step-dot--done' : ''"
            />
          </div>
          <div class="text-caption text-center text-medium-emphasis mb-4">
            Step {{ step }} of 3 —
            <span class="font-weight-medium">{{ stepLabels[step - 1] }}</span>
          </div>

          <v-card-text class="pa-2 pa-sm-4">

            <!-- Alert -->
            <v-alert
              v-if="error"
              type="error"
              rounded="lg"
              class="mb-4"
              density="compact"
              closable
              @click:close="error = null"
            >
              {{ error }}
            </v-alert>

            <!-- ── STEP 1: Personal info ──────────────── -->
            <div v-if="step === 1">
              <div class="text-subtitle-2 font-weight-bold mb-4">
                Personal Information
              </div>

              <v-text-field
                v-model="form.name"
                label="Full name"
                placeholder="e.g. Juan dela Cruz"
                prepend-inner-icon="mdi-account-outline"
                variant="outlined"
                rounded="lg"
                class="mb-3"
                :error-messages="fieldErrors.name"
              />

              <v-text-field
                v-model="form.email"
                label="Email address"
                type="email"
                placeholder="e.g. juan@email.com"
                prepend-inner-icon="mdi-email-outline"
                variant="outlined"
                rounded="lg"
                class="mb-3"
                :error-messages="fieldErrors.email"
              />

              <v-text-field
                v-model="form.phone"
                label="Mobile number (optional)"
                placeholder="e.g. 09171234567"
                prepend-inner-icon="mdi-phone-outline"
                variant="outlined"
                rounded="lg"
                class="mb-3"
              />

              <v-row>
                <v-col cols="12" sm="6">
                  <v-select
                    v-model="form.gender"
                    label="Gender"
                    :items="['Male', 'Female', 'Prefer not to say']"
                    prepend-inner-icon="mdi-gender-male-female"
                    variant="outlined"
                    rounded="lg"
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model="form.birthdate"
                    label="Date of birth"
                    type="date"
                    prepend-inner-icon="mdi-calendar-outline"
                    variant="outlined"
                    rounded="lg"
                  />
                </v-col>
              </v-row>
            </div>

            <!-- ── STEP 2: Address & Barangay ────────── -->
            <div v-if="step === 2">
              <div class="text-subtitle-2 font-weight-bold mb-4">
                Address & Barangay
              </div>

              <v-select
                v-model="form.barangay_id"
                label="Select your barangay"
                :items="barangayItems"
                item-title="label"
                item-value="id"
                prepend-inner-icon="mdi-home-city-outline"
                variant="outlined"
                rounded="lg"
                class="mb-3"
                :error-messages="fieldErrors.barangay_id"
              />

              <v-text-field
                v-model="form.address"
                label="Street address"
                placeholder="House No., Street Name"
                prepend-inner-icon="mdi-map-marker-outline"
                variant="outlined"
                rounded="lg"
                class="mb-3"
              />

              <!-- Selected barangay info card -->
              <v-card
                v-if="selectedBarangay"
                rounded="lg"
                color="primary"
                variant="tonal"
                flat
                class="pa-3"
              >
                <div class="d-flex align-center gap-3">
                  <v-icon color="primary">mdi-information-outline</v-icon>
                  <div>
                    <div class="text-body-2 font-weight-bold">
                      {{ selectedBarangay.name }}
                    </div>
                    <div class="text-caption text-medium-emphasis">
                      {{ selectedBarangay.municipality }},
                      {{ selectedBarangay.province }}
                    </div>
                  </div>
                </div>
              </v-card>
            </div>

            <!-- ── STEP 3: Account credentials ────────── -->
            <div v-if="step === 3">
              <div class="text-subtitle-2 font-weight-bold mb-4">
                Create Password
              </div>

              <v-text-field
                v-model="form.password"
                label="Password"
                :type="showPassword ? 'text' : 'password'"
                prepend-inner-icon="mdi-lock-outline"
                :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                variant="outlined"
                rounded="lg"
                class="mb-3"
                :error-messages="fieldErrors.password"
                @click:append-inner="showPassword = !showPassword"
              />

              <v-text-field
                v-model="form.password_confirmation"
                label="Confirm password"
                :type="showPassword ? 'text' : 'password'"
                prepend-inner-icon="mdi-lock-check-outline"
                variant="outlined"
                rounded="lg"
                class="mb-4"
                :error-messages="fieldErrors.password_confirmation"
              />

              <!-- Password strength -->
              <div class="mb-4">
                <div class="d-flex justify-space-between mb-1">
                  <span class="text-caption">Password strength</span>
                  <span class="text-caption font-weight-bold" :class="`text-${strengthColor}`">
                    {{ strengthLabel }}
                  </span>
                </div>
                <v-progress-linear
                  :model-value="passwordStrength"
                  :color="strengthColor"
                  rounded
                  height="6"
                />
              </div>

              <!-- Terms -->
              <v-checkbox
                v-model="form.agreed"
                density="compact"
                class="mt-2"
                :error-messages="fieldErrors.agreed"
              >
                <template #label>
                  <span class="text-body-2">
                    I agree to the
                    <a href="#" class="text-primary">Terms of Service</a>
                    and
                    <a href="#" class="text-primary">Privacy Policy</a>
                  </span>
                </template>
              </v-checkbox>
            </div>

          </v-card-text>

          <v-card-actions class="pa-4 pt-0 d-flex gap-2">
            <v-btn
              v-if="step > 1"
              variant="tonal"
              rounded="lg"
              size="large"
              prepend-icon="mdi-arrow-left"
              @click="step--"
            >
              Back
            </v-btn>
            <v-spacer/>
            <v-btn
              v-if="step < 3"
              color="primary"
              variant="flat"
              rounded="lg"
              size="large"
              append-icon="mdi-arrow-right"
              @click="nextStep"
            >
              Continue
            </v-btn>
            <v-btn
              v-if="step === 3"
              color="primary"
              variant="flat"
              rounded="lg"
              size="large"
              :loading="loading"
              prepend-icon="mdi-check"
              @click="handleRegister"
            >
              Create Account
            </v-btn>
          </v-card-actions>

          <div class="text-center pb-4 text-body-2">
            Already have an account?
            <router-link to="/login" class="text-primary font-weight-bold">
              Sign in
            </router-link>
          </div>

        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import axios from 'axios'
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router  = useRouter()
const auth    = useAuthStore()

const step        = ref(1)
const loading     = ref(false)
const error       = ref(null)
const showPassword = ref(false)
const barangays   = ref([])
const fieldErrors = ref({})

const stepLabels = ['Personal Info', 'Address & Barangay', 'Create Password']

const form = ref({
  name:                  '',
  email:                 '',
  phone:                 '',
  gender:                '',
  birthdate:             '',
  barangay_id:           null,
  address:               '',
  password:              '',
  password_confirmation: '',
  agreed:                false,
})

const barangayItems = computed(() =>
  barangays.value.map(b => ({
    id:    b.id,
    label: `${b.name} — ${b.municipality}, ${b.province}`,
  }))
)
const selectedBarangay = computed(() =>
  barangays.value.find(b => b.id === form.value.barangay_id) || null
)

// Password strength
const passwordStrength = computed(() => {
  const p = form.value.password
  if (!p) return 0
  let score = 0
  if (p.length >= 8)  score += 25
  if (p.length >= 12) score += 25
  if (/[A-Z]/.test(p)) score += 25
  if (/[0-9!@#$%]/.test(p)) score += 25
  return score
})

const strengthLabel = computed(() => {
  const s = passwordStrength.value
  if (s === 0)   return 'Enter password'
  if (s <= 25)   return 'Weak'
  if (s <= 50)   return 'Fair'
  if (s <= 75)   return 'Good'
  return 'Strong'
})

const strengthColor = computed(() => {
  const s = passwordStrength.value
  if (s <= 25) return 'error'
  if (s <= 50) return 'warning'
  if (s <= 75) return 'info'
  return 'success'
})

function nextStep() {
  fieldErrors.value = {}
  if (step.value === 1) {
    if (!form.value.name)  { fieldErrors.value.name  = ['Full name is required']; return }
    if (!form.value.email) { fieldErrors.value.email = ['Email is required'];     return }
  }
  if (step.value === 2) {
    if (!form.value.barangay_id) {
      fieldErrors.value.barangay_id = ['Please select your barangay']
      return
    }
  }
  step.value++
}

async function handleRegister() {
  fieldErrors.value = {}
  if (!form.value.agreed) {
    fieldErrors.value.agreed = ['You must agree to the terms']
    return
  }
  if (form.value.password !== form.value.password_confirmation) {
    fieldErrors.value.password_confirmation = ['Passwords do not match']
    return
  }
  loading.value = true
  error.value   = null
  try {
    await auth.register({
      name:                  form.value.name,
      email:                 form.value.email,
      password:              form.value.password,
      password_confirmation: form.value.password_confirmation,
      barangay_id:           form.value.barangay_id,
    })
    router.push('/dashboard')
  } catch (e) {
    const errors = e.response?.data?.errors
    if (errors) {
      fieldErrors.value = errors
      // Go back to the step with the error
      if (errors.name || errors.email) step.value = 1
      else if (errors.barangay_id)     step.value = 2
    } else {
      error.value = e.response?.data?.message || 'Registration failed.'
    }
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  try {
    const { data } = await axios.get('/api/barangays')
    barangays.value = data.barangays
  } catch {
    // fallback — use hardcoded if API not ready
    barangays.value = [
      { id: 1, name: 'Barangay Uno', municipality: 'Calamba', province: 'Laguna' },
    ]
  }
})
</script>

<style scoped>
.register-bg {
  background: linear-gradient(135deg, #E8EAF6 0%, #E3F2FD 50%, #F3E5F5 100%);
}
.step-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #E0E0E0;
  transition: all 0.3s;
}
.step-dot--active {
  background: #1A237E;
  width: 24px;
  border-radius: 4px;
}
.step-dot--done {
  background: #2E7D32;
}
</style>

