<template>
  <div>
    <!-- Header -->
    <div class="d-flex align-center justify-space-between mb-3 flex-wrap gap-2">
      <div>
        <div class="text-subtitle-1 font-weight-bold">Services</div>
        <div class="text-caption text-medium-emphasis">
          Request barangay documents and certificates
        </div>
      </div>
      <v-btn
        color="primary"
        rounded="lg"
        size="small"
        prepend-icon="mdi-history"
        variant="tonal"
        to="/requests"
      >
        My Requests
      </v-btn>
    </div>

    <!-- Service cards grid -->
    <v-row dense>
      <v-col
        v-for="service in services"
        :key="service.type"
        cols="12" sm="6" md="4"
      >
        <v-card
          rounded="xl"
          flat
          border
          class="service-card h-100"
          @click="openRequest(service)"
        >
          <v-card-text class="pa-3">
            <!-- Top row -->
            <div class="d-flex align-start justify-space-between mb-2">
              <v-avatar :color="service.bgColor" size="40" rounded="xl">
                <v-icon :color="service.color" size="20">{{ service.icon }}</v-icon>
              </v-avatar>
              <v-chip :color="service.color" size="x-small" variant="tonal">
                {{ service.duration }}
              </v-chip>
            </div>

            <div class="text-caption font-weight-bold mb-1">{{ service.title }}</div>
            <div class="text-caption text-medium-emphasis mb-2" style="font-size:11px;line-height:1.4">
              {{ service.description }}
            </div>

            <v-divider class="mb-2"/>

            <div class="d-flex align-center justify-space-between">
              <div class="text-caption text-medium-emphasis" style="font-size:10px">
                <v-icon size="12" class="mr-1">mdi-file-outline</v-icon>
                {{ service.requirements }}
              </div>
              <v-btn
                :color="service.color"
                size="x-small"
                variant="tonal"
                rounded="lg"
                append-icon="mdi-arrow-right"
              >
                Request
              </v-btn>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Request Dialog -->
    <v-dialog v-model="dialog" max-width="480" persistent>
      <v-card rounded="xl" elevation="0" border>
        <v-card-title class="pa-4 pb-2">
          <div class="d-flex align-center gap-2">
            <v-avatar
              v-if="selectedService"
              :color="selectedService.bgColor"
              size="38"
              rounded="lg"
            >
              <v-icon :color="selectedService.color" size="20">
                {{ selectedService.icon }}
              </v-icon>
            </v-avatar>
            <div>
              <div class="text-body-2 font-weight-bold">{{ selectedService?.title }}</div>
              <div class="text-caption text-medium-emphasis">Fill in the details below</div>
            </div>
          </div>
        </v-card-title>

        <v-card-text class="pa-4 pt-2">
          <v-alert
            v-if="submitError"
            type="error"
            density="compact"
            rounded="lg"
            class="mb-3"
            closable
            @click:close="submitError = null"
          >
            {{ submitError }}
          </v-alert>

          <!-- Purpose -->
          <v-textarea
            v-model="requestForm.purpose"
            label="Purpose of request"
            placeholder="e.g. For employment purposes, for scholarship application..."
            variant="outlined"
            rounded="lg"
            rows="3"
            counter="500"
            :error-messages="purposeError"
            class="mb-2"
            density="compact"
          />

          <!-- Requirements info -->
          <v-card rounded="lg" color="blue-lighten-5" flat class="pa-2">
            <div class="d-flex gap-2">
              <v-icon color="primary" size="16" class="mt-0">mdi-information-outline</v-icon>
              <div>
                <div class="text-caption font-weight-bold text-primary mb-1">Requirements</div>
                <div
                  v-for="req in selectedService?.requirementsList"
                  :key="req"
                  class="text-caption text-medium-emphasis"
                  style="font-size:11px"
                >
                  • {{ req }}
                </div>
              </div>
            </div>
          </v-card>

          <div class="text-caption text-medium-emphasis mt-2" style="font-size:10px">
            <v-icon size="12">mdi-clock-outline</v-icon>
            Processing time: {{ selectedService?.duration }}
          </div>
        </v-card-text>

        <v-card-actions class="pa-4 pt-0 gap-2">
          <v-btn variant="tonal" rounded="lg" size="small" @click="closeDialog">
            Cancel
          </v-btn>
          <v-spacer/>
          <v-btn
            color="primary"
            variant="flat"
            rounded="lg"
            size="small"
            :loading="submitting"
            prepend-icon="mdi-send"
            @click="submitRequest"
          >
            Submit Request
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Success snackbar -->
    <v-snackbar
      v-model="snackbar"
      color="success"
      rounded="lg"
      location="bottom right"
      :timeout="4000"
    >
      <v-icon class="mr-2" size="18">mdi-check-circle</v-icon>
      <span class="text-caption">Request submitted! Ref: {{ lastReference }}</span>
      <template #actions>
        <v-btn variant="text" size="small" @click="snackbar = false">Close</v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script setup>
import axios from 'axios'
import { ref } from 'vue'

const dialog          = ref(false)
const submitting      = ref(false)
const submitError     = ref(null)
const purposeError    = ref(null)
const snackbar        = ref(false)
const lastReference   = ref('')
const selectedService = ref(null)
const requestForm     = ref({ purpose: '' })

const services = [
  {
    type:             'clearance',
    title:            'Barangay Clearance',
    description:      'General clearance for employment, business, or legal purposes.',
    icon:             'mdi-file-certificate-outline',
    color:            'primary',
    bgColor:          'blue-lighten-5',
    duration:         '1-2 days',
    requirements:     '2 requirements',
    requirementsList: ['Valid government ID', 'Proof of residency'],
  },
  {
    type:             'residency',
    title:            'Certificate of Residency',
    description:      'Proof that you are a legitimate resident of the barangay.',
    icon:             'mdi-home-account',
    color:            'success',
    bgColor:          'green-lighten-5',
    duration:         '1 day',
    requirements:     '2 requirements',
    requirementsList: ['Valid government ID', 'Utility bill or lease contract'],
  },
  {
    type:             'indigency',
    title:            'Certificate of Indigency',
    description:      'For residents who need financial assistance or benefits.',
    icon:             'mdi-hand-heart-outline',
    color:            'warning',
    bgColor:          'orange-lighten-5',
    duration:         '1 day',
    requirements:     '3 requirements',
    requirementsList: ['Valid government ID', 'Proof of residency', 'Sworn statement'],
  },
  {
    type:             'business_permit',
    title:            'Business Permit Clearance',
    description:      'Required clearance before applying for a business permit.',
    icon:             'mdi-store-outline',
    color:            'purple',
    bgColor:          'purple-lighten-5',
    duration:         '2-3 days',
    requirements:     '3 requirements',
    requirementsList: ['Valid government ID', 'DTI/SEC registration', 'Sketch of business location'],
  },
  {
    type:             'good_moral',
    title:            'Certificate of Good Moral',
    description:      'Character reference for school, work, or legal applications.',
    icon:             'mdi-shield-star-outline',
    color:            'teal',
    bgColor:          'teal-lighten-5',
    duration:         '1 day',
    requirements:     '2 requirements',
    requirementsList: ['Valid government ID', 'Proof of residency'],
  },
  {
    type:             'blotter',
    title:            'Blotter Report',
    description:      'Official record of a complaint or incident in the barangay.',
    icon:             'mdi-alert-circle-outline',
    color:            'error',
    bgColor:          'red-lighten-5',
    duration:         'Same day',
    requirements:     '2 requirements',
    requirementsList: ['Valid government ID', 'Written incident report'],
  },
]

function openRequest(service) {
  selectedService.value = service
  requestForm.value     = { purpose: '' }
  submitError.value     = null
  purposeError.value    = null
  dialog.value          = true
}

function closeDialog() {
  dialog.value          = false
  selectedService.value = null
}

async function submitRequest() {
  purposeError.value = null
  submitError.value  = null

  if (!requestForm.value.purpose || requestForm.value.purpose.length < 10) {
    purposeError.value = 'Please describe your purpose (at least 10 characters)'
    return
  }

  submitting.value = true
  try {
    const { data } = await axios.post('/api/requests', {
      type:    selectedService.value.type,
      purpose: requestForm.value.purpose,
    })
    lastReference.value = data.request.reference_number
    snackbar.value      = true
    closeDialog()
  } catch (e) {
    submitError.value = e.response?.data?.message || 'Submission failed. Please try again.'
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
.service-card {
  cursor: pointer;
  transition: transform 0.18s ease, box-shadow 0.18s ease;
}
.service-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0,0,0,0.09) !important;
}
</style>