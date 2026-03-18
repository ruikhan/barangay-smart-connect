<template>
  <div>
    <!-- Access denied -->
    <div v-if="!auth.isAdmin && !auth.isStaff" class="text-center py-16">
      <v-icon size="64" color="error">mdi-shield-off-outline</v-icon>
      <div class="text-h6 font-weight-bold mt-4">Access Denied</div>
      <div class="text-body-2 text-medium-emphasis mt-2">
        You need admin or staff access to view this page.
      </div>
      <v-btn color="primary" variant="tonal" rounded="lg" class="mt-4" to="/dashboard">
        Go to Dashboard
      </v-btn>
    </div>

    <div v-else>
      <!-- Header -->
      <div class="d-flex align-center justify-space-between mb-6 flex-wrap gap-3">
        <div>
          <div class="text-h6 font-weight-bold">Admin Panel</div>
          <div class="text-body-2 text-medium-emphasis">
            Manage requests, residents, and announcements
          </div>
        </div>
        <v-chip :color="auth.isAdmin ? 'error' : 'warning'" variant="tonal" prepend-icon="mdi-shield-crown">
          {{ auth.user?.role?.toUpperCase() }}
        </v-chip>
      </div>

      <!-- Stats -->
      <v-row class="mb-6">
        <v-col v-for="stat in stats" :key="stat.title" cols="6" md="3">
          <v-card rounded="xl" flat border class="pa-4 text-center">
            <v-icon :color="stat.color" size="32" class="mb-2">{{ stat.icon }}</v-icon>
            <div class="text-h5 font-weight-bold">{{ stat.value }}</div>
            <div class="text-caption text-medium-emphasis">{{ stat.title }}</div>
          </v-card>
        </v-col>
      </v-row>

      <!-- Tabs -->
      <v-card rounded="xl" flat border>
        <v-tabs v-model="tab" color="primary" class="px-2">
          <v-tab value="requests" prepend-icon="mdi-clipboard-list-outline">
            Requests
          </v-tab>
          <v-tab value="residents" prepend-icon="mdi-account-group-outline">
            Residents
          </v-tab>
          <v-tab value="announce" prepend-icon="mdi-bullhorn-outline">
            Post News
          </v-tab>
        </v-tabs>

        <v-divider/>

        <v-window v-model="tab">

          <!-- ── REQUESTS TAB ────────────────────────── -->
          <v-window-item value="requests">
            <div class="pa-4">
              <!-- Status filter -->
              <div class="d-flex gap-2 mb-4 flex-wrap">
                <v-btn
                  v-for="s in statusFilters"
                  :key="s.value"
                  :color="statusFilter === s.value ? 'primary' : 'default'"
                  :variant="statusFilter === s.value ? 'flat' : 'tonal'"
                  rounded="xl"
                  size="small"
                  @click="filterStatus(s.value)"
                >
                  {{ s.label }}
                </v-btn>
              </div>

              <div v-if="loadingRequests" class="text-center py-8">
                <v-progress-circular indeterminate color="primary"/>
              </div>

              <div v-else-if="filteredRequests.length === 0" class="text-center py-8">
                <v-icon size="48" color="grey-lighten-2">mdi-clipboard-text-outline</v-icon>
                <div class="text-body-2 text-medium-emphasis mt-2">No requests found</div>
              </div>

              <v-table v-else density="comfortable" class="rounded-lg">
                <thead>
                  <tr>
                    <th>Reference</th>
                    <th>Resident</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="req in filteredRequests" :key="req.id">
                    <td class="text-caption font-weight-bold">{{ req.reference_number }}</td>
                    <td>
                      <div class="text-body-2 font-weight-medium">{{ req.resident_name }}</div>
                      <div class="text-caption text-medium-emphasis">{{ req.resident_email }}</div>
                    </td>
                    <td class="text-body-2">{{ req.type_label }}</td>
                    <td>
                      <v-chip :color="req.status_color" size="x-small" variant="tonal">
                        {{ req.status }}
                      </v-chip>
                    </td>
                    <td class="text-caption">{{ req.created_at }}</td>
                    <td>
                      <v-btn
                        size="x-small"
                        variant="tonal"
                        color="primary"
                        rounded="lg"
                        @click="openUpdateDialog(req)"
                      >
                        Update
                      </v-btn>
                    </td>
                  </tr>
                </tbody>
              </v-table>
            </div>
          </v-window-item>

          <!-- ── RESIDENTS TAB ──────────────────────── -->
          <v-window-item value="residents">
            <div class="pa-4">
              <div v-if="loadingResidents" class="text-center py-8">
                <v-progress-circular indeterminate color="primary"/>
              </div>

              <v-table v-else density="comfortable" class="rounded-lg">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="res in residents" :key="res.id">
                    <td>
                      <div class="d-flex align-center gap-2">
                        <v-avatar size="32" color="primary" rounded="lg">
                          <span class="text-caption font-weight-bold text-white">
                            {{ res.name?.charAt(0) }}
                          </span>
                        </v-avatar>
                        <span class="text-body-2 font-weight-medium">{{ res.name }}</span>
                      </div>
                    </td>
                    <td class="text-caption">{{ res.email }}</td>
                    <td>
                      <v-chip size="x-small" color="success" variant="tonal">
                        {{ res.role }}
                      </v-chip>
                    </td>
                    <td class="text-caption">{{ res.created_at }}</td>
                  </tr>
                </tbody>
              </v-table>
            </div>
          </v-window-item>

          <!-- ── POST ANNOUNCEMENT TAB ──────────────── -->
          <v-window-item value="announce">
            <div class="pa-4" style="max-width:600px">
              <v-alert
                v-if="announceSuccess"
                type="success"
                rounded="lg"
                class="mb-4"
                density="compact"
                closable
                @click:close="announceSuccess = false"
              >
                Announcement posted successfully!
              </v-alert>

              <v-text-field
                v-model="announceForm.title"
                label="Title"
                variant="outlined"
                rounded="lg"
                class="mb-3"
                :error-messages="announceErrors.title"
              />

              <v-textarea
                v-model="announceForm.content"
                label="Content"
                variant="outlined"
                rounded="lg"
                rows="4"
                class="mb-3"
                :error-messages="announceErrors.content"
              />

              <v-row>
                <v-col cols="12" sm="6">
                  <v-select
                    v-model="announceForm.category"
                    label="Category"
                    :items="announceCategories"
                    variant="outlined"
                    rounded="lg"
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-select
                    v-model="announceForm.priority"
                    label="Priority"
                    :items="['normal','important','urgent']"
                    variant="outlined"
                    rounded="lg"
                  />
                </v-col>
              </v-row>

              <v-checkbox
                v-model="announceForm.is_pinned"
                label="Pin this announcement"
                density="compact"
                class="mb-3"
              />

              <v-btn
                color="primary"
                variant="flat"
                rounded="lg"
                size="large"
                :loading="posting"
                prepend-icon="mdi-send"
                @click="postAnnouncement"
              >
                Post Announcement
              </v-btn>
            </div>
          </v-window-item>

        </v-window>
      </v-card>
    </div>

    <!-- Update request dialog -->
    <v-dialog v-model="updateDialog" max-width="440" persistent>
      <v-card rounded="xl" elevation="0" border>
        <v-card-title class="pa-5 pb-2">
          <div class="text-subtitle-1 font-weight-bold">Update Request Status</div>
          <div class="text-caption text-medium-emphasis">
            {{ selectedRequest?.reference_number }}
          </div>
        </v-card-title>
        <v-card-text class="pa-5 pt-2">
          <v-select
            v-model="updateForm.status"
            label="New status"
            :items="['pending','processing','approved','rejected','released']"
            variant="outlined"
            rounded="lg"
            class="mb-3"
          />
          <v-textarea
            v-model="updateForm.remarks"
            label="Remarks (optional)"
            variant="outlined"
            rounded="lg"
            rows="3"
          />
        </v-card-text>
        <v-card-actions class="pa-4 pt-0 gap-2">
          <v-btn variant="tonal" rounded="lg" @click="updateDialog = false">Cancel</v-btn>
          <v-spacer/>
          <v-btn
            color="primary"
            variant="flat"
            rounded="lg"
            :loading="updating"
            @click="confirmUpdate"
          >
            Save Changes
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

  </div>
</template>

<script setup>
import axios from 'axios'
import { computed, onMounted, ref } from 'vue'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()

const tab              = ref('requests')
const loadingRequests  = ref(false)
const loadingResidents = ref(false)
const posting          = ref(false)
const updating         = ref(false)
const announceSuccess  = ref(false)
const updateDialog     = ref(false)
const statusFilter     = ref('all')

const allRequests   = ref([])
const residents     = ref([])
const selectedRequest = ref(null)
const announceErrors  = ref({})

const stats = ref([
  { title: 'Total Residents',  value: '—', icon: 'mdi-account-group-outline',    color: 'primary' },
  { title: 'Total Requests',   value: '—', icon: 'mdi-clipboard-list-outline',   color: 'warning' },
  { title: 'Pending',          value: '—', icon: 'mdi-clock-outline',             color: 'error'   },
  { title: 'Announcements',    value: '—', icon: 'mdi-bullhorn-outline',           color: 'success' },
])

const statusFilters = [
  { value: 'all',        label: 'All'        },
  { value: 'pending',    label: 'Pending'    },
  { value: 'processing', label: 'Processing' },
  { value: 'approved',   label: 'Approved'   },
  { value: 'rejected',   label: 'Rejected'   },
  { value: 'released',   label: 'Released'   },
]

const announceCategories = [
  'general', 'emergency', 'health', 'infrastructure', 'event', 'livelihood'
]

const announceForm = ref({
  title: '', content: '', category: 'general',
  priority: 'normal', is_pinned: false,
})

const updateForm = ref({ status: '', remarks: '' })

const filteredRequests = computed(() => {
  if (statusFilter.value === 'all') return allRequests.value
  return allRequests.value.filter(r => r.status === statusFilter.value)
})

function filterStatus(s) { statusFilter.value = s }

function openUpdateDialog(req) {
  selectedRequest.value = req
  updateForm.value = { status: req.status, remarks: req.remarks || '' }
  updateDialog.value = true
}

async function confirmUpdate() {
  updating.value = true
  try {
    await axios.patch(`/api/admin/requests/${selectedRequest.value.id}/status`, updateForm.value)
    await fetchRequests()
    updateDialog.value = false
  } catch (e) {
    console.error(e)
  } finally {
    updating.value = false
  }
}

async function postAnnouncement() {
  announceErrors.value = {}
  if (!announceForm.value.title)   { announceErrors.value.title   = ['Title is required'];   return }
  if (!announceForm.value.content) { announceErrors.value.content = ['Content is required']; return }
  posting.value = true
  try {
    await axios.post('/api/admin/announcements', announceForm.value)
    announceSuccess.value = true
    announceForm.value = { title: '', content: '', category: 'general', priority: 'normal', is_pinned: false }
  } catch (e) {
    console.error(e)
  } finally {
    posting.value = false
  }
}

async function fetchStats() {
  try {
    const { data } = await axios.get('/api/admin/stats')
    stats.value[0].value = data.stats.total_residents
    stats.value[1].value = data.stats.total_requests
    stats.value[2].value = data.stats.pending_requests
    stats.value[3].value = data.stats.announcements
  } catch (e) { console.error(e) }
}

async function fetchRequests() {
  loadingRequests.value = true
  try {
    const { data } = await axios.get('/api/admin/requests')
    allRequests.value = data.requests
  } catch (e) { console.error(e) }
  finally { loadingRequests.value = false }
}

async function fetchResidents() {
  loadingResidents.value = true
  try {
    const { data } = await axios.get('/api/admin/residents')
    residents.value = data.residents
  } catch (e) { console.error(e) }
  finally { loadingResidents.value = false }
}

onMounted(async () => {
  if (auth.isAdmin || auth.isStaff) {
    await Promise.all([fetchStats(), fetchRequests(), fetchResidents()])
  }
})
</script>