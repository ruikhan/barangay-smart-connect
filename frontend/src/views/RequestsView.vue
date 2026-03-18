<template>
  <div>
    <!-- Header -->
    <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-2">
      <div>
        <div class="text-subtitle-1 font-weight-bold">My Requests</div>
        <div class="text-caption text-medium-emphasis">
          Track your document request status
        </div>
      </div>
      <v-btn
        color="primary"
        rounded="lg"
        size="small"
        prepend-icon="mdi-plus"
        variant="flat"
        to="/services"
      >
        New Request
      </v-btn>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="d-flex justify-center py-10">
      <v-progress-circular indeterminate color="primary" size="36"/>
    </div>

    <!-- Empty state -->
    <div v-else-if="requests.length === 0" class="text-center py-12">
      <v-icon size="56" color="grey-lighten-2">mdi-clipboard-text-outline</v-icon>
      <div class="text-body-2 font-weight-medium mt-3">No requests yet</div>
      <div class="text-caption text-medium-emphasis mt-1">
        Request a document from the barangay to get started
      </div>
      <v-btn
        color="primary"
        variant="flat"
        rounded="lg"
        size="small"
        class="mt-4"
        prepend-icon="mdi-file-plus-outline"
        to="/services"
      >
        Browse Services
      </v-btn>
    </div>

    <div v-else>
      <!-- Status summary chips -->
      <div class="d-flex gap-2 mb-4 flex-wrap">
        <v-chip
          v-for="s in statusSummary"
          :key="s.status"
          :color="s.color"
          variant="tonal"
          size="x-small"
          class="font-weight-medium"
        >
          {{ s.count }} {{ s.label }}
        </v-chip>
      </div>

      <!-- Request cards -->
      <v-row dense>
        <v-col
          v-for="req in requests"
          :key="req.id"
          cols="12" md="6"
        >
          <v-card
            rounded="xl"
            flat
            border
            class="request-card h-100"
            @click="openDetail(req)"
          >
            <v-card-text class="pa-3">
              <!-- Top row -->
              <div class="d-flex align-start justify-space-between mb-2">
                <div class="d-flex align-center gap-2">
                  <v-avatar :color="typeColor(req.type).bg" size="36" rounded="lg">
                    <v-icon :color="typeColor(req.type).color" size="18">
                      {{ typeIcon(req.type) }}
                    </v-icon>
                  </v-avatar>
                  <div>
                    <div class="text-caption font-weight-bold">{{ req.type_label }}</div>
                    <div class="text-caption text-medium-emphasis" style="font-size:10px">
                      {{ req.barangay }}
                    </div>
                  </div>
                </div>
                <v-chip :color="req.status_color" size="x-small" variant="tonal" class="font-weight-medium">
                  {{ req.status }}
                </v-chip>
              </div>

              <v-divider class="mb-2"/>

              <!-- Reference number -->
              <div class="d-flex align-center justify-space-between mb-1">
                <span class="text-caption text-medium-emphasis" style="font-size:10px">Reference No.</span>
                <span class="text-caption font-weight-bold font-mono" style="font-size:10px">
                  {{ req.reference_number }}
                </span>
              </div>

              <!-- Purpose -->
              <div class="d-flex align-start justify-space-between mb-1">
                <span class="text-caption text-medium-emphasis" style="font-size:10px">Purpose</span>
                <span
                  class="text-caption text-right ml-4"
                  style="font-size:10px;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                >
                  {{ req.purpose }}
                </span>
              </div>

              <!-- Date -->
              <div class="d-flex align-center justify-space-between">
                <span class="text-caption text-medium-emphasis" style="font-size:10px">Submitted</span>
                <span class="text-caption" style="font-size:10px">{{ req.created_at }}</span>
              </div>

              <!-- Progress stepper -->
              <div class="mt-3">
                <div class="d-flex align-center justify-space-between">
                  <div
                    v-for="(step, i) in statusSteps"
                    :key="step.value"
                    class="d-flex align-center flex-1"
                  >
                    <div class="text-center flex-1">
                      <v-avatar :color="stepColor(req.status, step.value)" size="20" class="mb-1">
                        <v-icon size="10" color="white">
                          {{ stepIcon(req.status, step.value) }}
                        </v-icon>
                      </v-avatar>
                      <div class="text-caption" style="font-size:8px;line-height:1.2">
                        {{ step.label }}
                      </div>
                    </div>
                    <div
                      v-if="i < statusSteps.length - 1"
                      class="flex-1 mx-1"
                      style="height:2px;border-radius:1px"
                      :style="{
                        background: isStepPassed(req.status, statusSteps[i+1].value)
                          ? '#2E7D32' : '#E0E0E0'
                      }"
                    />
                  </div>
                </div>
              </div>

            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </div>

    <!-- Detail Dialog -->
    <v-dialog v-model="dialog" max-width="500" scrollable>
      <v-card v-if="selected" rounded="xl" elevation="0" border>
        <v-card-title class="pa-4 pb-2">
          <div class="d-flex align-center gap-2">
            <v-avatar :color="typeColor(selected.type).bg" size="40" rounded="lg">
              <v-icon :color="typeColor(selected.type).color" size="22">
                {{ typeIcon(selected.type) }}
              </v-icon>
            </v-avatar>
            <div>
              <div class="text-body-2 font-weight-bold">{{ selected.type_label }}</div>
              <v-chip :color="selected.status_color" size="x-small" variant="tonal" class="mt-1">
                {{ selected.status?.toUpperCase() }}
              </v-chip>
            </div>
          </div>
        </v-card-title>

        <v-divider/>

        <v-card-text class="pa-4">
          <div
            v-for="info in detailRows(selected)"
            :key="info.label"
            class="d-flex justify-space-between py-1"
            style="border-bottom:1px solid rgba(0,0,0,0.05)"
          >
            <span class="text-caption text-medium-emphasis">{{ info.label }}</span>
            <span class="text-caption font-weight-medium text-right ml-4">
              {{ info.value || '—' }}
            </span>
          </div>

          <div v-if="selected.remarks" class="mt-3">
            <div class="text-caption text-medium-emphasis mb-1">Remarks from staff</div>
            <v-card rounded="lg" color="blue-lighten-5" flat class="pa-2">
              <div class="text-caption">{{ selected.remarks }}</div>
            </v-card>
          </div>

          <div class="mt-3">
            <div class="text-caption text-medium-emphasis mb-2">Status Timeline</div>
            <v-timeline density="compact" side="end">
              <v-timeline-item dot-color="success" size="x-small">
                <div class="text-caption font-weight-medium">Request submitted</div>
                <div class="text-caption text-medium-emphasis" style="font-size:10px">
                  {{ selected.created_at }}
                </div>
              </v-timeline-item>
              <v-timeline-item v-if="selected.processed_at" dot-color="info" size="x-small">
                <div class="text-caption font-weight-medium">Processing started</div>
                <div class="text-caption text-medium-emphasis" style="font-size:10px">
                  {{ selected.processed_at }}
                </div>
              </v-timeline-item>
              <v-timeline-item v-if="selected.approved_at" dot-color="success" size="x-small">
                <div class="text-caption font-weight-medium">Request approved</div>
                <div class="text-caption text-medium-emphasis" style="font-size:10px">
                  {{ selected.approved_at }}
                </div>
              </v-timeline-item>
            </v-timeline>
          </div>
        </v-card-text>

        <v-card-actions class="pa-3 pt-0">
          <v-spacer/>
          <v-btn rounded="lg" size="small" variant="tonal" @click="dialog = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import axios from 'axios'
import { computed, onMounted, ref } from 'vue'

const loading  = ref(false)
const requests = ref([])
const dialog   = ref(false)
const selected = ref(null)

const statusSteps = [
  { value: 'pending',    label: 'Submitted'  },
  { value: 'processing', label: 'Processing' },
  { value: 'approved',   label: 'Approved'   },
  { value: 'released',   label: 'Released'   },
]

const statusOrder = ['pending', 'processing', 'approved', 'released']

function stepColor(currentStatus, stepValue) {
  if (currentStatus === 'rejected') return stepValue === 'pending' ? 'error' : 'grey-lighten-2'
  const currentIdx = statusOrder.indexOf(currentStatus)
  const stepIdx    = statusOrder.indexOf(stepValue)
  if (stepIdx < currentIdx)  return 'success'
  if (stepIdx === currentIdx) return 'primary'
  return 'grey-lighten-2'
}

function stepIcon(currentStatus, stepValue) {
  if (currentStatus === 'rejected' && stepValue === 'pending') return 'mdi-close'
  const currentIdx = statusOrder.indexOf(currentStatus)
  const stepIdx    = statusOrder.indexOf(stepValue)
  if (stepIdx <= currentIdx) return 'mdi-check'
  return 'mdi-circle-small'
}

function isStepPassed(currentStatus, stepValue) {
  return statusOrder.indexOf(stepValue) <= statusOrder.indexOf(currentStatus)
}

const statusSummary = computed(() => {
  const counts = {}
  requests.value.forEach(r => { counts[r.status] = (counts[r.status] || 0) + 1 })
  const colorMap = {
    pending: 'warning', processing: 'info',
    approved: 'success', released: 'success', rejected: 'error',
  }
  return Object.entries(counts).map(([status, count]) => ({
    status, count, label: status, color: colorMap[status] || 'grey',
  }))
})

function typeColor(type) {
  const map = {
    clearance:       { color: 'primary', bg: 'blue-lighten-5'   },
    residency:       { color: 'success', bg: 'green-lighten-5'  },
    indigency:       { color: 'warning', bg: 'orange-lighten-5' },
    business_permit: { color: 'purple',  bg: 'purple-lighten-5' },
    good_moral:      { color: 'teal',    bg: 'teal-lighten-5'   },
    blotter:         { color: 'error',   bg: 'red-lighten-5'    },
  }
  return map[type] || { color: 'primary', bg: 'blue-lighten-5' }
}

function typeIcon(type) {
  const map = {
    clearance:       'mdi-file-certificate-outline',
    residency:       'mdi-home-account',
    indigency:       'mdi-hand-heart-outline',
    business_permit: 'mdi-store-outline',
    good_moral:      'mdi-shield-star-outline',
    blotter:         'mdi-alert-circle-outline',
  }
  return map[type] || 'mdi-file-outline'
}

function detailRows(req) {
  return [
    { label: 'Reference No.',  value: req.reference_number },
    { label: 'Document Type',  value: req.type_label       },
    { label: 'Status',         value: req.status           },
    { label: 'Purpose',        value: req.purpose          },
    { label: 'Barangay',       value: req.barangay         },
    { label: 'Date Submitted', value: req.created_at       },
    { label: 'Date Processed', value: req.processed_at     },
    { label: 'Date Approved',  value: req.approved_at      },
  ]
}

function openDetail(req) {
  selected.value = req
  dialog.value   = true
}

async function fetchRequests() {
  loading.value = true
  try {
    const { data } = await axios.get('/api/requests')
    requests.value = data.requests
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

onMounted(fetchRequests)
</script>

<style scoped>
.request-card {
  cursor: pointer;
  transition: transform 0.15s, box-shadow 0.15s;
}
.request-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0,0,0,0.08) !important;
}
.font-mono { font-family: monospace; }
</style>