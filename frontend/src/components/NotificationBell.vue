<template>
  <div class="notif-wrapper" ref="wrapperRef">
    <!-- Bell button -->
    <button class="bell-btn" @click="togglePanel" :class="{ active: isOpen }">
      <v-icon size="22">mdi-bell-outline</v-icon>
      <transition name="badge-pop">
        <span v-if="store.unreadCount > 0" class="badge">
          {{ store.unreadCount > 99 ? '99+' : store.unreadCount }}
        </span>
      </transition>
    </button>

    <!-- Notification panel -->
    <transition name="panel-drop">
      <div v-if="isOpen" class="notif-panel">

        <!-- Panel header -->
        <div class="panel-header">
          <div class="panel-title">
            <span>Notifications</span>
            <span v-if="store.unreadCount > 0" class="unread-badge">
              {{ store.unreadCount }} new
            </span>
          </div>
          <button
            v-if="store.unreadCount > 0"
            class="read-all-btn"
            @click="store.markAllRead()"
          >
            Mark all read
          </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="panel-loading">
          <div class="loading-dot" v-for="i in 3" :key="i"/>
        </div>

        <!-- Empty state -->
        <div v-else-if="store.notifications.length === 0" class="panel-empty">
          <v-icon size="36" color="grey-lighten-2">mdi-bell-sleep-outline</v-icon>
          <p>You're all caught up!</p>
        </div>

        <!-- Notification list -->
        <div v-else class="notif-list">
          <div
            v-for="notif in store.notifications"
            :key="notif.id"
            class="notif-item"
            :class="{ unread: !notif.is_read }"
            @click="handleClick(notif)"
          >
            <!-- Icon -->
            <div class="notif-icon-wrap" :class="`color-${notif.color}`">
              <v-icon size="16">{{ notif.icon }}</v-icon>
            </div>

            <!-- Content -->
            <div class="notif-content">
              <div class="notif-title">{{ notif.title }}</div>
              <div class="notif-body" v-if="notif.body">{{ notif.body }}</div>
              <div class="notif-time">{{ notif.time_ago }}</div>
            </div>

            <!-- Unread dot -->
            <div v-if="!notif.is_read" class="unread-dot"/>

            <!-- Delete -->
            <button
              class="delete-btn"
              @click.stop="store.deleteNotification(notif.id)"
            >
              <v-icon size="14">mdi-close</v-icon>
            </button>
          </div>
        </div>

      </div>
    </transition>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, ref } from 'vue'
import { useNotificationStore } from '../stores/notifications'

const store     = useNotificationStore()
const isOpen    = ref(false)
const loading   = ref(false)
const wrapperRef = ref(null)

async function togglePanel() {
  isOpen.value = !isOpen.value
  if (isOpen.value && store.notifications.length === 0) {
    loading.value = true
    await store.fetchNotifications()
    loading.value = false
  } else if (isOpen.value) {
    store.fetchNotifications()
  }
}

async function handleClick(notif) {
  if (!notif.is_read) {
    await store.markRead(notif.id)
  }
}

// Close on outside click
function handleOutsideClick(e) {
  if (wrapperRef.value && !wrapperRef.value.contains(e.target)) {
    isOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleOutsideClick)
  store.startPolling()
})

onUnmounted(() => {
  document.removeEventListener('click', handleOutsideClick)
  store.stopPolling()
})
</script>

<style scoped>
.notif-wrapper {
  position: relative;
  display: inline-flex;
}

/* ── Bell button ── */
.bell-btn {
  position: relative;
  background: none;
  border: none;
  cursor: pointer;
  width: 38px;
  height: 38px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: rgba(0,0,0,0.6);
  transition: background 0.2s;
}
.bell-btn:hover, .bell-btn.active {
  background: rgba(0,0,0,0.06);
}

/* Badge */
.badge {
  position: absolute;
  top: 4px;
  right: 4px;
  background: #ef4444;
  color: white;
  font-size: 9px;
  font-weight: 700;
  min-width: 16px;
  height: 16px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 3px;
  border: 2px solid white;
  line-height: 1;
}
.badge-pop-enter-active { animation: badgePop 0.3s cubic-bezier(0.34,1.56,0.64,1); }
.badge-pop-leave-active { animation: badgePop 0.15s ease reverse; }
@keyframes badgePop {
  from { transform: scale(0); opacity: 0; }
  to   { transform: scale(1); opacity: 1; }
}

/* ── Panel ── */
.notif-panel {
  position: absolute;
  top: calc(100% + 8px);
  right: -8px;
  width: 340px;
  max-height: 480px;
  background: #fff;
  border-radius: 16px;
  box-shadow:
    0 20px 60px rgba(0,0,0,0.15),
    0 4px 16px rgba(0,0,0,0.08),
    0 0 0 1px rgba(0,0,0,0.06);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  z-index: 9999;
}

.panel-drop-enter-active {
  animation: dropIn 0.25s cubic-bezier(0.34,1.3,0.64,1);
}
.panel-drop-leave-active {
  animation: dropIn 0.15s ease reverse;
}
@keyframes dropIn {
  from { transform: translateY(-8px) scale(0.97); opacity: 0; transform-origin: top right; }
  to   { transform: translateY(0) scale(1); opacity: 1; }
}

/* ── Panel header ── */
.panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 16px 12px;
  border-bottom: 1px solid rgba(0,0,0,0.06);
  flex-shrink: 0;
}
.panel-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 700;
  color: #1f2937;
}
.unread-badge {
  background: #eff6ff;
  color: #1d4ed8;
  font-size: 10px;
  font-weight: 600;
  padding: 2px 7px;
  border-radius: 10px;
}
.read-all-btn {
  background: none;
  border: none;
  font-size: 11px;
  color: #6b7280;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 6px;
  transition: all 0.2s;
}
.read-all-btn:hover {
  background: #f3f4f6;
  color: #1a3a5c;
}

/* ── Loading ── */
.panel-loading {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 6px;
  padding: 32px;
}
.loading-dot {
  width: 8px; height: 8px;
  background: #d1d5db;
  border-radius: 50%;
  animation: loadDot 1.2s ease-in-out infinite;
}
.loading-dot:nth-child(2) { animation-delay: 0.2s; }
.loading-dot:nth-child(3) { animation-delay: 0.4s; }
@keyframes loadDot {
  0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
  30% { transform: translateY(-6px); opacity: 1; }
}

/* ── Empty ── */
.panel-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
  gap: 8px;
}
.panel-empty p {
  font-size: 13px;
  color: #9ca3af;
  margin: 0;
}

/* ── List ── */
.notif-list {
  overflow-y: auto;
  flex: 1;
}
.notif-list::-webkit-scrollbar { width: 3px; }
.notif-list::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 3px; }

.notif-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 12px 14px;
  cursor: pointer;
  transition: background 0.15s;
  position: relative;
  border-bottom: 1px solid rgba(0,0,0,0.04);
}
.notif-item:last-child { border-bottom: none; }
.notif-item:hover { background: #f9fafb; }
.notif-item.unread { background: #eff6ff; }
.notif-item.unread:hover { background: #dbeafe; }

/* Notif icon */
.notif-icon-wrap {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.color-primary  { background: #eff6ff; color: #1d4ed8 !important; }
.color-warning  { background: #fffbeb; color: #d97706 !important; }
.color-success  { background: #f0fdf4; color: #16a34a !important; }
.color-info     { background: #f0f9ff; color: #0284c7 !important; }
.color-error    { background: #fef2f2; color: #dc2626 !important; }

/* Content */
.notif-content { flex: 1; min-width: 0; }
.notif-title {
  font-size: 12.5px;
  font-weight: 600;
  color: #1f2937;
  line-height: 1.4;
  margin-bottom: 2px;
}
.notif-body {
  font-size: 11.5px;
  color: #6b7280;
  line-height: 1.45;
  margin-bottom: 4px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.notif-time {
  font-size: 10px;
  color: #9ca3af;
}

/* Unread dot */
.unread-dot {
  width: 7px; height: 7px;
  background: #3b82f6;
  border-radius: 50%;
  flex-shrink: 0;
  margin-top: 4px;
}

/* Delete button */
.delete-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: #d1d5db;
  opacity: 0;
  padding: 2px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  transition: all 0.15s;
  flex-shrink: 0;
}
.notif-item:hover .delete-btn { opacity: 1; }
.delete-btn:hover { color: #ef4444; background: #fee2e2; }

/* ── Mobile ── */
@media (max-width: 480px) {
  .notif-panel {
    position: fixed;
    top: 58px;
    left: 12px;
    right: 12px;
    width: auto;
    max-height: 70dvh;
    border-radius: 14px;
  }
}
</style>