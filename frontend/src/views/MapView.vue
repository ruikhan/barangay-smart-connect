<template>
  <div>
    <!-- Header -->
    <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-3">
      <div>
        <div class="text-h6 font-weight-bold">Incident Map</div>
        <div class="text-body-2 text-medium-emphasis">
          View and report incidents in your barangay
        </div>
      </div>
      <v-btn
        color="error"
        variant="flat"
        rounded="lg"
        prepend-icon="mdi-map-marker-plus"
        @click="reportDialog = true"
      >
        Report Incident
      </v-btn>
    </div>

    <!-- Category legend -->
    <div class="d-flex gap-2 mb-4 flex-wrap">
      <v-chip
        v-for="cat in categories"
        :key="cat.value"
        :color="cat.color"
        variant="tonal"
        size="small"
        :prepend-icon="cat.icon"
        :class="activeCategory === cat.value ? 'opacity-100' : 'opacity-60'"
        @click="toggleCategory(cat.value)"
      >
        {{ cat.label }}
      </v-chip>
    </div>

    <!-- Map container -->
    <v-card rounded="xl" flat border overflow-hidden>
      <div id="map" style="height:480px;width:100%;z-index:1"/>
    </v-card>

    <!-- Incident list -->
    <div class="mt-5">
      <div class="text-subtitle-2 font-weight-bold mb-3">
        Recent Incidents
        <v-chip size="x-small" color="error" variant="tonal" class="ml-2">
          {{ filteredReports.length }}
        </v-chip>
      </div>

      <div v-if="loading" class="text-center py-8">
        <v-progress-circular indeterminate color="primary" size="32"/>
      </div>

      <div v-else-if="filteredReports.length === 0" class="text-center py-8">
        <v-icon size="48" color="grey-lighten-2">mdi-map-marker-off-outline</v-icon>
        <div class="text-body-2 text-medium-emphasis mt-2">
          No incidents reported yet
        </div>
      </div>

      <v-row v-else>
        <v-col
          v-for="report in filteredReports"
          :key="report.id"
          cols="12" sm="6" md="4"
        >
          <v-card
            rounded="xl"
            flat
            border
            class="incident-card"
            @click="focusReport(report)"
          >
            <v-card-text class="pa-3">
              <div class="d-flex align-start gap-2">
                <v-avatar
                  :color="severityBg(report.severity)"
                  size="36"
                  rounded="lg"
                >
                  <v-icon
                    :color="severityColor(report.severity)"
                    size="18"
                  >
                    {{ report.category_icon }}
                  </v-icon>
                </v-avatar>
                <div class="flex-1 min-width-0">
                  <div class="d-flex align-center gap-1 mb-1">
                    <v-chip
                      :color="report.severity_color"
                      size="x-small"
                      variant="flat"
                    >
                      {{ report.severity }}
                    </v-chip>
                    <v-chip size="x-small" variant="tonal" color="grey">
                      {{ report.category }}
                    </v-chip>
                  </div>
                  <div class="text-body-2 font-weight-bold text-truncate">
                    {{ report.title }}
                  </div>
                  <div class="text-caption text-medium-emphasis mt-1 text-truncate">
                    <v-icon size="10">mdi-map-marker-outline</v-icon>
                    {{ report.address || 'Location pinned on map' }}
                  </div>
                  <div class="text-caption text-medium-emphasis">
                    <v-icon size="10">mdi-clock-outline</v-icon>
                    {{ report.time_ago }}
                  </div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </div>

    <!-- Report Dialog -->
    <v-dialog v-model="reportDialog" max-width="540" persistent>
      <v-card rounded="xl" elevation="0" border>
        <v-card-title class="pa-5 pb-2">
          <div class="text-subtitle-1 font-weight-bold">Report an Incident</div>
          <div class="text-caption text-medium-emphasis">
            Click on the map to pin the exact location
          </div>
        </v-card-title>

        <v-card-text class="pa-5 pt-2">
          <v-alert
            v-if="reportError"
            type="error"
            density="compact"
            rounded="lg"
            class="mb-3"
            closable
            @click:close="reportError = null"
          >
            {{ reportError }}
          </v-alert>

          <!-- Mini map for pinning -->
          <v-card rounded="lg" flat border class="mb-3 overflow-hidden">
            <div id="report-map" style="height:200px;width:100%;z-index:1"/>
            <div
              v-if="!reportForm.latitude"
              class="text-center text-caption text-medium-emphasis pa-2"
              style="background:rgba(0,0,0,0.03)"
            >
              <v-icon size="14">mdi-gesture-tap</v-icon>
              Click on the map above to pin the incident location
            </div>
            <div v-else class="text-center text-caption text-success pa-2"
              style="background:rgba(46,125,50,0.05)"
            >
              <v-icon size="14" color="success">mdi-check-circle</v-icon>
              Location pinned: {{ reportForm.latitude?.toFixed(5) }},
              {{ reportForm.longitude?.toFixed(5) }}
            </div>
          </v-card>

          <v-text-field
            v-model="reportForm.title"
            label="Incident title"
            placeholder="e.g. Flooded road near plaza"
            variant="outlined"
            rounded="lg"
            class="mb-3"
            :error-messages="reportErrors.title"
          />

          <v-textarea
            v-model="reportForm.description"
            label="Description"
            placeholder="Describe the incident in detail..."
            variant="outlined"
            rounded="lg"
            rows="3"
            class="mb-3"
            :error-messages="reportErrors.description"
          />

          <v-row>
            <v-col cols="12" sm="6">
              <v-select
                v-model="reportForm.category"
                label="Category"
                :items="categoryItems"
                variant="outlined"
                rounded="lg"
                :error-messages="reportErrors.category"
              />
            </v-col>
            <v-col cols="12" sm="6">
              <v-select
                v-model="reportForm.severity"
                label="Severity"
                :items="['low','moderate','high','critical']"
                variant="outlined"
                rounded="lg"
              />
            </v-col>
          </v-row>

          <v-text-field
            v-model="reportForm.address"
            label="Address / Landmark (optional)"
            prepend-inner-icon="mdi-map-marker-outline"
            variant="outlined"
            rounded="lg"
          />
        </v-card-text>

        <v-card-actions class="pa-5 pt-0 gap-2">
          <v-btn variant="tonal" rounded="lg" size="large" @click="closeReportDialog">
            Cancel
          </v-btn>
          <v-spacer/>
          <v-btn
            color="error"
            variant="flat"
            rounded="lg"
            size="large"
            :loading="submitting"
            prepend-icon="mdi-send"
            @click="submitReport"
          >
            Submit Report
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
      <v-icon class="mr-2">mdi-check-circle</v-icon>
      Incident reported successfully!
    </v-snackbar>
  </div>
</template>

<script setup>
import axios from 'axios'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'

// Fix Leaflet default marker icons
delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
  iconUrl:       'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
  shadowUrl:     'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
})

const loading      = ref(false)
const reports      = ref([])
const reportDialog = ref(false)
const submitting   = ref(false)
const snackbar     = ref(false)
const reportError  = ref(null)
const reportErrors = ref({})
const activeCategory = ref('all')

// Calamba, Laguna coordinates as default
const DEFAULT_LAT = 14.2113
const DEFAULT_LNG = 121.1653

let mainMap   = null
let reportMap = null
let markers   = []
let pinMarker = null

const reportForm = ref({
  title: '', description: '', category: 'other',
  severity: 'moderate', latitude: null, longitude: null, address: '',
})

const categories = [
  { value: 'all',            label: 'All',            icon: 'mdi-map',           color: 'primary' },
  { value: 'flood',          label: 'Flood',          icon: 'mdi-water-alert',   color: 'blue'    },
  { value: 'fire',           label: 'Fire',           icon: 'mdi-fire',          color: 'error'   },
  { value: 'accident',       label: 'Accident',       icon: 'mdi-car-emergency', color: 'warning' },
  { value: 'crime',          label: 'Crime',          icon: 'mdi-shield-alert',  color: 'purple'  },
  { value: 'infrastructure', label: 'Infrastructure', icon: 'mdi-road-variant',  color: 'brown'   },
  { value: 'health',         label: 'Health',         icon: 'mdi-medical-bag',   color: 'success' },
]

const categoryItems = [
  'flood', 'fire', 'accident', 'crime', 'infrastructure', 'health', 'other'
]

const filteredReports = computed(() => {
  if (activeCategory.value === 'all') return reports.value
  return reports.value.filter(r => r.category === activeCategory.value)
})

function toggleCategory(val) {
  activeCategory.value = activeCategory.value === val ? 'all' : val
  refreshMarkers()
}

function severityColor(s) {
  return { low: 'success', moderate: 'warning', high: 'error', critical: 'error' }[s] || 'grey'
}

function severityBg(s) {
  return {
    low: 'green-lighten-5', moderate: 'orange-lighten-5',
    high: 'red-lighten-5',  critical: 'red-lighten-5',
  }[s] || 'grey-lighten-5'
}

function markerColor(severity) {
  return { low: '#2E7D32', moderate: '#F57F17', high: '#C62828', critical: '#B71C1C' }[severity] || '#555'
}

function createColoredIcon(severity) {
  const color = markerColor(severity)
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="28" height="36" viewBox="0 0 28 36">
    <path fill="${color}" d="M14 0C6.3 0 0 6.3 0 14c0 10.5 14 22 14 22s14-11.5 14-22C28 6.3 21.7 0 14 0z"/>
    <circle fill="white" cx="14" cy="14" r="6"/>
  </svg>`
  return L.divIcon({
    html: svg,
    className: '',
    iconSize: [28, 36],
    iconAnchor: [14, 36],
    popupAnchor: [0, -36],
  })
}

function initMainMap() {
  if (mainMap) return
  mainMap = L.map('map').setView([DEFAULT_LAT, DEFAULT_LNG], 14)
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(mainMap)
  refreshMarkers()
}

function refreshMarkers() {
  if (!mainMap) return
  markers.forEach(m => mainMap.removeLayer(m))
  markers = []
  filteredReports.value.forEach(r => {
    if (!r.latitude || !r.longitude) return
    const marker = L.marker([r.latitude, r.longitude], {
      icon: createColoredIcon(r.severity)
    })
    .bindPopup(`
      <div style="min-width:180px">
        <strong>${r.title}</strong><br>
        <span style="color:#666;font-size:12px">${r.category} · ${r.severity}</span><br>
        <span style="font-size:12px">${r.description?.substring(0, 80)}...</span><br>
        <span style="color:#999;font-size:11px">${r.time_ago}</span>
      </div>
    `)
    .addTo(mainMap)
    markers.push(marker)
  })
}

function focusReport(report) {
  if (!mainMap || !report.latitude) return
  mainMap.flyTo([report.latitude, report.longitude], 17, { duration: 1 })
  const marker = markers.find((_, i) => filteredReports.value[i]?.id === report.id)
  marker?.openPopup()
}

function initReportMap() {
  nextTick(() => {
    setTimeout(() => {
      if (reportMap) {
        reportMap.remove()
        reportMap = null
      }
      reportMap = L.map('report-map').setView([DEFAULT_LAT, DEFAULT_LNG], 14)
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
      }).addTo(reportMap)

      reportMap.on('click', (e) => {
        const { lat, lng } = e.latlng
        reportForm.value.latitude  = lat
        reportForm.value.longitude = lng
        if (pinMarker) reportMap.removeLayer(pinMarker)
        pinMarker = L.marker([lat, lng]).addTo(reportMap)
      })
    }, 300)
  })
}

function closeReportDialog() {
  reportDialog.value = false
  reportForm.value = {
    title: '', description: '', category: 'other',
    severity: 'moderate', latitude: null, longitude: null, address: '',
  }
  reportErrors.value = {}
  reportError.value  = null
  if (pinMarker && reportMap) {
    reportMap.removeLayer(pinMarker)
    pinMarker = null
  }
}

async function submitReport() {
  reportErrors.value = {}
  reportError.value  = null

  if (!reportForm.value.latitude) {
    reportError.value = 'Please click on the map to pin the incident location.'
    return
  }

  submitting.value = true
  try {
    const { data } = await axios.post('/api/incidents', reportForm.value)
    reports.value.unshift(data.report)
    refreshMarkers()
    snackbar.value = true
    closeReportDialog()
  } catch (e) {
    reportErrors.value = e.response?.data?.errors || {}
    reportError.value  = e.response?.data?.message || 'Failed to submit report.'
  } finally {
    submitting.value = false
  }
}

async function fetchReports() {
  loading.value = true
  try {
    const { data } = await axios.get('/api/incidents')
    reports.value = data.reports
    refreshMarkers()
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

watch(reportDialog, (val) => {
  if (val) initReportMap()
})

watch(filteredReports, () => refreshMarkers())

onMounted(async () => {
  await fetchReports()
  nextTick(() => initMainMap())
})

onUnmounted(() => {
  if (mainMap)   { mainMap.remove();   mainMap   = null }
  if (reportMap) { reportMap.remove(); reportMap = null }
})
</script>

<style scoped>
.incident-card {
  cursor: pointer;
  transition: transform 0.15s, box-shadow 0.15s;
}
.incident-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0,0,0,0.08) !important;
}
</style>