<template>
  <div>
    <!-- Header -->
    <div class="mb-6">
      <div class="text-h6 font-weight-bold">My Profile</div>
      <div class="text-body-2 text-medium-emphasis">
        Manage your account information
      </div>
    </div>

    <v-row>
      <!-- Left — Profile card -->
      <v-col cols="12" md="4">
        <v-card rounded="xl" flat border class="pa-5 text-center mb-4">
          <!-- Avatar -->
          <div class="position-relative d-inline-block mb-4">
            <v-avatar size="96" color="primary" rounded="xl" class="elevation-4">
              <span class="text-h4 font-weight-bold text-white">
                {{ auth.user?.name?.charAt(0)?.toUpperCase() }}
              </span>
            </v-avatar>
            <v-btn
              icon
              size="x-small"
              color="primary"
              class="position-absolute"
              style="bottom:-4px;right:-4px"
              elevation="2"
            >
              <v-icon size="14">mdi-pencil</v-icon>
            </v-btn>
          </div>

          <div class="text-h6 font-weight-bold">{{ auth.user?.name }}</div>
          <div class="text-body-2 text-medium-emphasis mb-3">
            {{ auth.user?.email }}
          </div>

          <v-chip
            :color="roleColor"
            variant="tonal"
            size="small"
            class="mb-4"
            prepend-icon="mdi-shield-account-outline"
          >
            {{ auth.user?.role?.toUpperCase() }}
          </v-chip>

          <v-divider class="mb-4"/>

          <!-- Barangay info -->
          <v-card rounded="lg" color="blue-lighten-5" flat class="pa-3 text-left">
            <div class="d-flex align-center gap-2 mb-1">
              <v-icon color="primary" size="18">mdi-home-city-outline</v-icon>
              <span class="text-caption font-weight-bold text-primary">
                Barangay Info
              </span>
            </div>
            <div class="text-body-2 font-weight-medium">
              {{ auth.user?.barangay?.name || 'Not assigned' }}
            </div>
            <div class="text-caption text-medium-emphasis">
              {{ auth.user?.barangay?.municipality }}
            </div>
          </v-card>

          <!-- Stats -->
          <v-row class="mt-4" dense>
            <v-col cols="6">
              <v-card rounded="lg" flat color="grey-lighten-4" class="pa-3 text-center">
                <div class="text-h6 font-weight-bold text-primary">
                  {{ requestCount }}
                </div>
                <div class="text-caption text-medium-emphasis">Requests</div>
              </v-card>
            </v-col>
            <v-col cols="6">
              <v-card rounded="lg" flat color="grey-lighten-4" class="pa-3 text-center">
                <div class="text-h6 font-weight-bold text-success">
                  {{ approvedCount }}
                </div>
                <div class="text-caption text-medium-emphasis">Approved</div>
              </v-card>
            </v-col>
          </v-row>
        </v-card>
      </v-col>

      <!-- Right — Edit forms -->
      <v-col cols="12" md="8">

        <!-- Personal info -->
        <v-card rounded="xl" flat border class="mb-4">
          <v-card-title class="pa-5 pb-3">
            <div class="text-subtitle-1 font-weight-bold">Personal Information</div>
          </v-card-title>
          <v-divider/>
          <v-card-text class="pa-5">
            <v-alert
              v-if="profileSuccess"
              type="success"
              density="compact"
              rounded="lg"
              class="mb-4"
              closable
              @click:close="profileSuccess = false"
            >
              Profile updated successfully!
            </v-alert>

            <v-row>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="profileForm.name"
                  label="Full name"
                  prepend-inner-icon="mdi-account-outline"
                  variant="outlined"
                  rounded="lg"
                  :error-messages="profileErrors.name"
                />
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="profileForm.email"
                  label="Email address"
                  type="email"
                  prepend-inner-icon="mdi-email-outline"
                  variant="outlined"
                  rounded="lg"
                  :error-messages="profileErrors.email"
                />
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="profileForm.phone"
                  label="Mobile number"
                  prepend-inner-icon="mdi-phone-outline"
                  variant="outlined"
                  rounded="lg"
                />
              </v-col>
              <v-col cols="12" sm="6">
                <v-select
                  v-model="profileForm.gender"
                  label="Gender"
                  :items="['Male','Female','Prefer not to say']"
                  prepend-inner-icon="mdi-gender-male-female"
                  variant="outlined"
                  rounded="lg"
                />
              </v-col>
              <v-col cols="12">
                <v-text-field
                  v-model="profileForm.address"
                  label="Street address"
                  prepend-inner-icon="mdi-map-marker-outline"
                  variant="outlined"
                  rounded="lg"
                />
              </v-col>
            </v-row>
          </v-card-text>
          <v-card-actions class="pa-5 pt-0">
            <v-spacer/>
            <v-btn
              color="primary"
              variant="flat"
              rounded="lg"
              :loading="savingProfile"
              prepend-icon="mdi-content-save-outline"
              @click="saveProfile"
            >
              Save Changes
            </v-btn>
          </v-card-actions>
        </v-card>

        <!-- Change password -->
        <v-card rounded="xl" flat border>
          <v-card-title class="pa-5 pb-3">
            <div class="text-subtitle-1 font-weight-bold">Change Password</div>
          </v-card-title>
          <v-divider/>
          <v-card-text class="pa-5">
            <v-alert
              v-if="passwordSuccess"
              type="success"
              density="compact"
              rounded="lg"
              class="mb-4"
              closable
              @click:close="passwordSuccess = false"
            >
              Password changed successfully!
            </v-alert>

            <v-alert
              v-if="passwordError"
              type="error"
              density="compact"
              rounded="lg"
              class="mb-4"
              closable
              @click:close="passwordError = null"
            >
              {{ passwordError }}
            </v-alert>

            <v-row>
              <v-col cols="12">
                <v-text-field
                  v-model="passwordForm.current_password"
                  label="Current password"
                  :type="showPass ? 'text' : 'password'"
                  prepend-inner-icon="mdi-lock-outline"
                  :append-inner-icon="showPass ? 'mdi-eye-off' : 'mdi-eye'"
                  variant="outlined"
                  rounded="lg"
                  :error-messages="passwordErrors.current_password"
                  @click:append-inner="showPass = !showPass"
                />
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="passwordForm.password"
                  label="New password"
                  :type="showPass ? 'text' : 'password'"
                  prepend-inner-icon="mdi-lock-plus-outline"
                  variant="outlined"
                  rounded="lg"
                  :error-messages="passwordErrors.password"
                />
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="passwordForm.password_confirmation"
                  label="Confirm new password"
                  :type="showPass ? 'text' : 'password'"
                  prepend-inner-icon="mdi-lock-check-outline"
                  variant="outlined"
                  rounded="lg"
                />
              </v-col>
            </v-row>
          </v-card-text>
          <v-card-actions class="pa-5 pt-0">
            <v-spacer/>
            <v-btn
              color="primary"
              variant="flat"
              rounded="lg"
              :loading="savingPassword"
              prepend-icon="mdi-lock-reset"
              @click="changePassword"
            >
              Update Password
            </v-btn>
          </v-card-actions>
        </v-card>

      </v-col>
    </v-row>
  </div>
</template>

<script setup>
import axios from 'axios'
import { computed, onMounted, ref } from 'vue'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()

const savingProfile  = ref(false)
const savingPassword = ref(false)
const profileSuccess = ref(false)
const passwordSuccess = ref(false)
const passwordError  = ref(null)
const showPass       = ref(false)
const profileErrors  = ref({})
const passwordErrors = ref({})
const requestCount   = ref(0)
const approvedCount  = ref(0)

const profileForm = ref({
  name:    auth.user?.name    || '',
  email:   auth.user?.email   || '',
  phone:   auth.user?.phone   || '',
  gender:  auth.user?.gender  || '',
  address: auth.user?.address || '',
})

const passwordForm = ref({
  current_password:      '',
  password:              '',
  password_confirmation: '',
})

const roleColor = computed(() => {
  const map = { admin: 'error', staff: 'warning', resident: 'success' }
  return map[auth.user?.role] || 'primary'
})

async function saveProfile() {
  profileErrors.value = {}
  savingProfile.value = true
  try {
    const { data } = await axios.put('/api/profile', profileForm.value)
    auth.user.name  = data.user.name
    auth.user.email = data.user.email
    localStorage.setItem('user', JSON.stringify(auth.user))
    profileSuccess.value = true
  } catch (e) {
    profileErrors.value = e.response?.data?.errors || {}
  } finally {
    savingProfile.value = false
  }
}

async function changePassword() {
  passwordErrors.value = {}
  passwordError.value  = null
  if (passwordForm.value.password !== passwordForm.value.password_confirmation) {
    passwordErrors.value.password = ['Passwords do not match']
    return
  }
  savingPassword.value = true
  try {
    await axios.put('/api/profile/password', passwordForm.value)
    passwordSuccess.value = true
    passwordForm.value = {
      current_password: '', password: '', password_confirmation: ''
    }
  } catch (e) {
    passwordError.value = e.response?.data?.message || 'Failed to update password.'
    passwordErrors.value = e.response?.data?.errors || {}
  } finally {
    savingPassword.value = false
  }
}

onMounted(async () => {
  try {
    const { data } = await axios.get('/api/requests')
    requestCount.value  = data.requests.length
    approvedCount.value = data.requests.filter(
      r => r.status === 'approved' || r.status === 'released'
    ).length
  } catch (e) { console.error(e) }
})
</script>