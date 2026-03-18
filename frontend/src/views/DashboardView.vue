<template>
  <div>
    <!-- Welcome banner -->
    <v-card rounded="xl" flat class="welcome-banner pa-3 pa-md-4 mb-4">
      <v-row align="center">
        <v-col cols="12" md="8">
          <div class="text-subtitle-1 font-weight-bold text-white text-wrap">
            Magandang araw, {{ auth.user?.name?.split(' ')[0] }}! 👋
          </div>
          <div class="text-caption mt-1" style="color:rgba(255,255,255,0.8)">
            {{ auth.user?.barangay?.name || 'Barangay' }} ·
            {{ auth.user?.role }} ·
            {{ new Date().toLocaleDateString('en-PH', { weekday:'long', year:'numeric', month:'long', day:'numeric' }) }}
          </div>
        </v-col>
        <v-col cols="12" md="4" class="text-md-right">
          <v-btn
            color="white"
            variant="flat"
            rounded="lg"
            size="small"
            prepend-icon="mdi-plus"
            to="/services"
          >
            New Request
          </v-btn>
        </v-col>
      </v-row>
    </v-card>

    <!-- Stat cards -->
    <v-row class="mb-3" dense>
      <v-col
        v-for="stat in stats"
        :key="stat.title"
        cols="6" sm="6" md="3"
      >
        <v-card rounded="xl" flat class="stat-card pa-3 h-100">
          <div class="d-flex align-center justify-space-between mb-2">
            <v-avatar :color="stat.bgColor" size="36" rounded="lg">
              <v-icon :color="stat.color" size="18">{{ stat.icon }}</v-icon>
            </v-avatar>
            <v-chip :color="stat.color" size="x-small" variant="tonal">
              {{ stat.change }}
            </v-chip>
          </div>
          <div class="text-h6 font-weight-bold">{{ stat.value }}</div>
          <div class="text-caption text-medium-emphasis" style="font-size:11px">{{ stat.title }}</div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Recent requests + Quick actions -->
    <v-row density="compact">
      <!-- Recent requests -->
      <v-col cols="12" md="8">
        <v-card rounded="xl" flat class="pa-3">
          <div class="d-flex align-center justify-space-between mb-3">
            <div class="text-body-2 font-weight-bold">Recent Requests</div>
            <v-btn variant="text" size="x-small" color="primary" to="/requests">
              View all
            </v-btn>
          </div>

          <div v-if="recentRequests.length === 0" class="text-center py-6">
            <v-icon size="40" color="grey-lighten-2">mdi-clipboard-text-outline</v-icon>
            <div class="text-caption text-medium-emphasis mt-2">No requests yet</div>
            <v-btn color="primary" variant="tonal" size="x-small" class="mt-2" to="/services">
              Make a request
            </v-btn>
          </div>

          <v-list v-else lines="two" class="pa-0">
            <v-list-item
              v-for="req in recentRequests"
              :key="req.id"
              rounded="lg"
              class="mb-1 request-item px-2"
              min-height="48"
            >
              <template #prepend>
                <v-avatar :color="req.bgColor" rounded="lg" size="34">
                  <v-icon :color="req.color" size="16">{{ req.icon }}</v-icon>
                </v-avatar>
              </template>
              <v-list-item-title class="text-caption font-weight-medium">
                {{ req.title }}
              </v-list-item-title>
              <v-list-item-subtitle style="font-size:10px">
                {{ req.date }}
              </v-list-item-subtitle>
              <template #append>
                <v-chip :color="req.statusColor" size="x-small" variant="tonal">
                  {{ req.status }}
                </v-chip>
              </template>
            </v-list-item>
          </v-list>
        </v-card>
      </v-col>

      <!-- Quick actions + Announcements -->
      <v-col cols="12" md="4">
        <!-- Quick actions -->
        <v-card rounded="xl" flat class="pa-3 mb-3">
          <div class="text-body-2 font-weight-bold mb-2">Quick Actions</div>
          <v-row density="compact">
            <v-col v-for="action in quickActions" :key="action.title" cols="6">
              <v-card
                rounded="xl"
                flat
                :color="action.bgColor"
                class="pa-2 text-center quick-action cursor-pointer"
                :to="action.to"
              >
                <v-icon :color="action.color" size="22" class="mb-1">
                  {{ action.icon }}
                </v-icon>
                <div class="text-caption font-weight-medium text-no-wrap" style="font-size:10px">
                  {{ action.title }}
                </div>
              </v-card>
            </v-col>
          </v-row>
        </v-card>

        <!-- Latest announcement -->
        <v-card rounded="xl" flat class="pa-3 announcement-card">
          <div class="text-body-2 font-weight-bold mb-2">
            Latest Announcement
          </div>
          <v-card rounded="lg" color="primary" variant="tonal" flat class="pa-2">
            <div class="text-caption text-medium-emphasis mb-1" style="font-size:10px">
              March 18, 2026
            </div>
            <div class="text-caption font-weight-medium">
              Community clean-up drive this Saturday
            </div>
            <div class="text-caption text-medium-emphasis mt-1" style="font-size:10px">
              All residents are invited to join...
            </div>
          </v-card>
          <v-btn
            variant="text"
            size="x-small"
            color="primary"
            class="mt-1"
            to="/announcements"
          >
            See all announcements →
          </v-btn>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()

const stats = [
  { title: 'My Requests',   value: '3',  icon: 'mdi-file-document-outline',  color: 'primary', bgColor: 'blue-lighten-5',  change: '+1 new'  },
  { title: 'Announcements', value: '5',  icon: 'mdi-bullhorn-outline',         color: 'warning', bgColor: 'orange-lighten-5', change: '2 today' },
  { title: 'Messages',      value: '1',  icon: 'mdi-message-outline',          color: 'success', bgColor: 'green-lighten-5',  change: 'unread'  },
  { title: 'Notifications', value: '2',  icon: 'mdi-bell-outline',             color: 'error',   bgColor: 'red-lighten-5',    change: 'new'     },
]

const recentRequests = ref([
  { id: 1, title: 'Barangay Clearance',       date: 'Mar 17, 2026', status: 'Approved', statusColor: 'success', icon: 'mdi-file-check-outline',    color: 'success', bgColor: 'green-lighten-5'  },
  { id: 2, title: 'Certificate of Residency', date: 'Mar 15, 2026', status: 'Pending',  statusColor: 'warning', icon: 'mdi-file-clock-outline',    color: 'warning', bgColor: 'orange-lighten-5' },
  { id: 3, title: 'Business Permit',          date: 'Mar 10, 2026', status: 'Review',   statusColor: 'error',   icon: 'mdi-file-search-outline',   color: 'error',   bgColor: 'red-lighten-5'    },
])

const quickActions = [
  { title: 'Request Doc',  icon: 'mdi-file-plus-outline',    color: 'primary', bgColor: 'blue-lighten-5',   to: '/services'      },
  { title: 'Report Issue', icon: 'mdi-alert-circle-outline', color: 'error',   bgColor: 'red-lighten-5',    to: '/map'           },
  { title: 'View News',    icon: 'mdi-newspaper-variant',    color: 'warning', bgColor: 'orange-lighten-5', to: '/announcements' },
  { title: 'Messages',     icon: 'mdi-message-plus-outline', color: 'success', bgColor: 'green-lighten-5',  to: '/messages'      },
]
</script>

<style scoped>
.welcome-banner {
  background: linear-gradient(135deg, #1A237E 0%, #1565C0 50%, #0288D1 100%);
}
.stat-card {
  border: 1px solid rgba(0,0,0,0.06);
  transition: transform 0.2s, box-shadow 0.2s;
}
.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0,0,0,0.08) !important;
}
.request-item {
  border: 1px solid rgba(0,0,0,0.05);
  transition: background 0.2s;
}
.request-item:hover { background: rgba(0,0,0,0.02); }
.quick-action { transition: transform 0.2s; }
.quick-action:hover { transform: translateY(-2px); }
.announcement-card { border: 1px solid rgba(0,0,0,0.06); }
</style>
