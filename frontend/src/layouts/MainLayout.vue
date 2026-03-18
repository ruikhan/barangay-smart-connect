<template>
  <v-app :theme="theme">

    <!-- ── SIDEBAR (desktop only, permanent) ─────────────── -->
    <v-navigation-drawer
      v-if="mdAndUp"
      v-model="drawer"
      :rail="rail"
      permanent
      class="sidebar"
    >
      <div class="sidebar-logo pa-3 d-flex align-center" :class="rail ? 'justify-center' : 'gap-3'">
        <v-icon size="28" color="white">mdi-home-city</v-icon>
        <div v-if="!rail">
          <div class="text-subtitle-2 font-weight-bold text-white" style="font-size:13px">Barangay</div>
          <div class="text-white" style="font-size:10px;opacity:0.7">Smart Connect</div>
        </div>
      </div>

      <v-divider color="rgba(255,255,255,0.15)"/>

      <div v-if="!rail" class="pa-2">
        <v-card rounded="lg" color="rgba(255,255,255,0.1)" flat class="pa-2">
          <div class="d-flex align-center gap-2">
            <v-avatar size="28" color="red-accent-2">
              <span style="font-size:11px;font-weight:700;color:white">
                {{ auth.user?.name?.charAt(0) }}
              </span>
            </v-avatar>
            <div class="flex-1 min-width-0">
              <div style="font-size:11px;font-weight:700;color:white" class="text-truncate">
                {{ auth.user?.name }}
              </div>
              <v-chip size="x-small" :color="roleColor" variant="flat" class="mt-1" style="font-size:9px;height:16px">
                {{ auth.user?.role }}
              </v-chip>
            </div>
          </div>
        </v-card>
      </div>

      <v-divider v-if="!rail" color="rgba(255,255,255,0.15)"/>

      <v-list density="compact" nav class="mt-1">
        <v-list-item
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          rounded="lg"
          active-class="nav-active"
          class="nav-item mb-1"
          min-height="36"
        >
          <template #prepend>
            <v-icon :icon="item.icon" size="18"/>
          </template>
          <v-list-item-title v-if="!rail" style="font-size:12px">{{ item.title }}</v-list-item-title>
          <v-tooltip v-if="rail" activator="parent" location="end">{{ item.title }}</v-tooltip>
        </v-list-item>
      </v-list>

      <template #append>
        <v-divider color="rgba(255,255,255,0.15)"/>
        <v-list density="compact" nav class="my-1">
          <v-list-item rounded="lg" class="nav-item" min-height="36" @click="toggleTheme">
            <template #prepend><v-icon icon="mdi-theme-light-dark" size="18"/></template>
            <v-list-item-title v-if="!rail" style="font-size:12px">Toggle theme</v-list-item-title>
          </v-list-item>
          <v-list-item rounded="lg" class="nav-item" min-height="36" @click="handleLogout">
            <template #prepend><v-icon icon="mdi-logout" size="18"/></template>
            <v-list-item-title v-if="!rail" style="font-size:12px">Sign out</v-list-item-title>
          </v-list-item>
        </v-list>
      </template>
    </v-navigation-drawer>

    <!-- ── MOBILE DRAWER (temporary overlay) ────────────── -->
    <v-navigation-drawer
      v-if="!mdAndUp"
      v-model="mobileDrawer"
      temporary
      class="sidebar"
      width="260"
    >
      <!-- Drawer header -->
      <div class="mobile-drawer-header pa-4">
        <div class="d-flex align-center gap-3 mb-3">
          <v-icon size="26" color="white">mdi-home-city</v-icon>
          <div>
            <div class="font-weight-bold text-white" style="font-size:13px">Barangay</div>
            <div class="text-white" style="font-size:10px;opacity:0.7">Smart Connect</div>
          </div>
        </div>
        <div class="d-flex align-center gap-2 mobile-user-card pa-2 rounded-lg">
          <v-avatar size="36" color="red-accent-2">
            <span class="font-weight-bold text-white" style="font-size:13px">
              {{ auth.user?.name?.charAt(0) }}
            </span>
          </v-avatar>
          <div>
            <div class="font-weight-bold text-white" style="font-size:12px">
              {{ auth.user?.name }}
            </div>
            <div class="text-white" style="font-size:10px;opacity:0.65">
              {{ auth.user?.barangay?.name }}
            </div>
          </div>
        </div>
      </div>

      <v-divider color="rgba(255,255,255,0.12)"/>

      <v-list density="compact" nav class="mt-2 px-2">
        <v-list-item
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          rounded="xl"
          active-class="nav-active"
          class="nav-item mb-1"
          min-height="42"
          @click="mobileDrawer = false"
        >
          <template #prepend>
            <v-icon :icon="item.icon" size="19"/>
          </template>
          <v-list-item-title style="font-size:13px">{{ item.title }}</v-list-item-title>
        </v-list-item>
      </v-list>

      <template #append>
        <v-divider color="rgba(255,255,255,0.12)"/>
        <v-list density="compact" nav class="my-2 px-2">
          <v-list-item rounded="xl" class="nav-item" min-height="42" @click="toggleTheme">
            <template #prepend><v-icon icon="mdi-theme-light-dark" size="19"/></template>
            <v-list-item-title style="font-size:13px">Toggle theme</v-list-item-title>
          </v-list-item>
          <v-list-item rounded="xl" class="nav-item" min-height="42" @click="handleLogout">
            <template #prepend><v-icon icon="mdi-logout" size="19"/></template>
            <v-list-item-title style="font-size:13px">Sign out</v-list-item-title>
          </v-list-item>
        </v-list>
      </template>
    </v-navigation-drawer>

    <!-- ── TOP APP BAR ───────────────────────────────────── -->
    <v-app-bar
      flat
      color="surface"
      border="b"
      :height="mdAndUp ? 56 : 50"
      elevation="0"
      class="top-bar"
    >
      <v-btn
        v-if="mdAndUp"
        :icon="rail ? 'mdi-menu' : 'mdi-menu-open'"
        variant="text"
        size="small"
        class="ml-1"
        @click="rail = !rail"
      />
      <v-app-bar-nav-icon
        v-if="!mdAndUp"
        size="small"
        @click="mobileDrawer = !mobileDrawer"
      />

      <v-app-bar-title>
        <span class="font-weight-bold text-primary" :style="mdAndUp ? 'font-size:14px' : 'font-size:13px'">
          {{ currentPageTitle }}
        </span>
      </v-app-bar-title>

      <template #append>
        <v-btn :size="mdAndUp ? 'default' : 'small'" icon variant="text" class="mr-1">
          <v-badge color="error" content="2" floating>
            <v-icon :size="mdAndUp ? 22 : 20">mdi-bell-outline</v-icon>
          </v-badge>
        </v-btn>
        <v-avatar :size="mdAndUp ? 32 : 28" color="primary" class="mr-2 cursor-pointer">
          <span class="font-weight-bold text-white" :style="mdAndUp ? 'font-size:12px' : 'font-size:11px'">
            {{ auth.user?.name?.charAt(0) }}
          </span>
        </v-avatar>
      </template>
    </v-app-bar>

    <!-- ── MAIN CONTENT ──────────────────────────────────── -->
    <v-main class="main-content">
      <v-container
        fluid
        :class="mdAndUp ? 'pa-5' : 'pa-2 pb-safe'"
      >
        <router-view />
      </v-container>
    </v-main>

    <!-- ── BOTTOM NAV (mobile only) ──────────────────────── -->
    <div v-if="!mdAndUp" class="mobile-bottom-nav">
      <div class="bottom-nav-inner">
        <router-link
          v-for="item in bottomNavItems"
          :key="item.to"
          :to="item.to"
          class="nav-tab"
          :class="{ active: route.path === item.to }"
        >
          <div class="nav-tab-pill">
            <v-icon :size="item.to === route.path ? 22 : 20">{{ item.icon }}</v-icon>
          </div>
          <span class="nav-tab-label">{{ item.title }}</span>
        </router-link>
      </div>
    </div>

    <!-- ── CHATBOT WIDGET ─────────────────────────────────── -->
    <ChatbotWidget />

  </v-app>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useDisplay } from 'vuetify'
import ChatbotWidget from '../components/ChatbotWidget.vue'
import { useAuthStore } from '../stores/auth'

const router       = useRouter()
const route        = useRoute()
const auth         = useAuthStore()
const { mdAndUp }  = useDisplay()

const drawer       = ref(true)
const rail         = ref(false)
const mobileDrawer = ref(false)
const theme        = ref('light')

function toggleTheme() {
  theme.value = theme.value === 'light' ? 'dark' : 'light'
}

async function handleLogout() {
  try {
    await auth.logout()
  } catch {
    auth.clearAuth()
  } finally {
    router.push('/login')
  }
}

const roleColor = computed(() => {
  const map = { admin: 'error', staff: 'warning', resident: 'success' }
  return map[auth.user?.role] || 'primary'
})

const currentPageTitle = computed(() => {
  const map = {
    '/dashboard':     'Dashboard',
    '/services':      'Services',
    '/requests':      'My Requests',
    '/announcements': 'Announcements',
    '/messages':      'Messages',
    '/map':           'Incident Map',
    '/admin':         'Admin Panel',
    '/profile':       'My Profile',
  }
  return map[route.path] || 'Barangay Smart Connect'
})

const navItems = computed(() => {
  const base = [
    { title: 'Dashboard',     icon: 'mdi-view-dashboard-outline', to: '/dashboard'     },
    { title: 'Services',      icon: 'mdi-file-document-outline',  to: '/services'      },
    { title: 'My Requests',   icon: 'mdi-clipboard-list-outline', to: '/requests'      },
    { title: 'Announcements', icon: 'mdi-bullhorn-outline',        to: '/announcements' },
    { title: 'Messages',      icon: 'mdi-message-outline',         to: '/messages'      },
    { title: 'Incident Map',  icon: 'mdi-map-marker-outline',      to: '/map'           },
  ]
  if (auth.isAdmin || auth.isStaff) {
    base.push({ title: 'Admin Panel', icon: 'mdi-shield-crown-outline', to: '/admin' })
  }
  return base
})

const bottomNavItems = [
  { title: 'Home',     icon: 'mdi-home-outline',          to: '/dashboard'     },
  { title: 'Services', icon: 'mdi-file-document-outline', to: '/services'      },
  { title: 'News',     icon: 'mdi-bullhorn-outline',       to: '/announcements' },
  { title: 'Messages', icon: 'mdi-message-outline',        to: '/messages'      },
  { title: 'Account',  icon: 'mdi-account-circle-outline', to: '/profile'       },
]
</script>

<style scoped>
/* ── Sidebar ── */
.sidebar {
  background: linear-gradient(175deg, #1A237E 0%, #283593 60%, #1565C0 100%) !important;
}
.sidebar-logo { min-height: 52px; }
.nav-item { color: rgba(255,255,255,0.72) !important; }
.nav-item:hover {
  background: rgba(255,255,255,0.1) !important;
  color: white !important;
}
.nav-active {
  background: rgba(255,255,255,0.18) !important;
  color: white !important;
}

/* ── Mobile drawer header ── */
.mobile-drawer-header {
  background: linear-gradient(160deg, #1A237E, #1565C0);
}
.mobile-user-card {
  background: rgba(255,255,255,0.1);
}

/* ── Top bar ── */
.top-bar { border-bottom: 1px solid rgba(0,0,0,0.07) !important; }

/* ── Main content ── */
.main-content { background: #F5F6FA; }

/* ── Mobile bottom nav ── */
.mobile-bottom-nav {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  background: #fff;
  border-top: 1px solid rgba(0,0,0,0.08);
  padding-bottom: env(safe-area-inset-bottom, 0px);
  box-shadow: 0 -4px 20px rgba(0,0,0,0.06);
}

.bottom-nav-inner {
  display: flex;
  align-items: center;
  justify-content: space-around;
  height: 58px;
  padding: 0 4px;
}

.nav-tab {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 2px;
  flex: 1;
  text-decoration: none;
  color: #9CA3AF;
  transition: color 0.2s ease;
  padding: 6px 2px;
  min-width: 0;
}

.nav-tab.active {
  color: #1A237E;
}

.nav-tab-pill {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 26px;
  border-radius: 13px;
  transition: background 0.2s ease, transform 0.15s ease;
}

.nav-tab.active .nav-tab-pill {
  background: rgba(26, 35, 126, 0.1);
  transform: scale(1.05);
}

.nav-tab-label {
  font-size: 9.5px;
  font-weight: 500;
  letter-spacing: 0.2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 52px;
  text-align: center;
}

.nav-tab.active .nav-tab-label {
  font-weight: 700;
}

/* ── Safe area padding for content ── */
.pb-safe {
  padding-bottom: calc(58px + env(safe-area-inset-bottom, 0px) + 8px) !important;
}

/* ── Dark theme adjustments ── */
.v-theme--dark .mobile-bottom-nav {
  background: #1e1e2e;
  border-top-color: rgba(255,255,255,0.08);
}
.v-theme--dark .nav-tab { color: #6B7280; }
.v-theme--dark .nav-tab.active { color: #90CAF9; }
.v-theme--dark .nav-tab.active .nav-tab-pill { background: rgba(144,202,249,0.12); }
.v-theme--dark .main-content { background: #12131a; }
</style>