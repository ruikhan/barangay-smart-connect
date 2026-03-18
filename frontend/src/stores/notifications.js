import axios from 'axios'
import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

export const useNotificationStore = defineStore('notifications', () => {
  const notifications = ref([])
  const unreadCount   = ref(0)
  let   pollInterval  = null

  const hasUnread = computed(() => unreadCount.value > 0)

  async function fetchNotifications() {
    try {
      const { data } = await axios.get('/api/notifications')
      notifications.value = data.notifications
      unreadCount.value   = data.unread_count
    } catch (e) { console.error(e) }
  }

  async function fetchUnreadCount() {
    try {
      const { data } = await axios.get('/api/notifications/unread-count')
      unreadCount.value = data.unread_count
    } catch (e) {}
  }

  async function markRead(id) {
    await axios.patch(`/api/notifications/${id}/read`)
    const n = notifications.value.find(n => n.id === id)
    if (n) { n.is_read = true; unreadCount.value = Math.max(0, unreadCount.value - 1) }
  }

  async function markAllRead() {
    await axios.patch('/api/notifications/read-all')
    notifications.value.forEach(n => (n.is_read = true))
    unreadCount.value = 0
  }

  async function deleteNotification(id) {
    await axios.delete(`/api/notifications/${id}`)
    const idx = notifications.value.findIndex(n => n.id === id)
    if (idx !== -1) {
      if (!notifications.value[idx].is_read) unreadCount.value = Math.max(0, unreadCount.value - 1)
      notifications.value.splice(idx, 1)
    }
  }

  function startPolling() {
    fetchUnreadCount()
    pollInterval = setInterval(fetchUnreadCount, 30000)
  }

  function stopPolling() {
    if (pollInterval) { clearInterval(pollInterval); pollInterval = null }
  }

  return { notifications, unreadCount, hasUnread, fetchNotifications, fetchUnreadCount, markRead, markAllRead, deleteNotification, startPolling, stopPolling }
})