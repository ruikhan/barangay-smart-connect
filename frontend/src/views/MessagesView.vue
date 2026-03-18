<template>
  <div>
    <div class="text-h6 font-weight-bold mb-1">Messages</div>
    <div class="text-body-2 text-medium-emphasis mb-5">Chat with barangay staff</div>
    <v-row style="height:calc(100vh - 220px);min-height:500px">
      <v-col cols="12" md="4" class="d-flex flex-column" style="height:100%">
        <v-card rounded="xl" flat border class="flex-1 d-flex flex-column overflow-hidden">
          <div class="pa-3 border-b">
            <v-btn block color="primary" variant="tonal" rounded="lg" prepend-icon="mdi-pencil-plus-outline" @click="newChatDialog = true">New Message</v-btn>
          </div>
          <div class="overflow-y-auto flex-1">
            <div v-if="loadingConvos" class="text-center pa-6"><v-progress-circular indeterminate color="primary" size="28"/></div>
            <div v-else-if="conversations.length === 0" class="text-center pa-6">
              <v-icon size="40" color="grey-lighten-2">mdi-message-outline</v-icon>
              <div class="text-body-2 text-medium-emphasis mt-2">No conversations yet</div>
            </div>
            <v-list v-else nav density="compact" class="pa-2">
              <v-list-item v-for="convo in conversations" :key="convo.user?.id" rounded="lg" class="mb-1" :active="activeUser?.id === convo.user?.id"color="primary" @click="openThread(convo.user)">
                <template #prepend>
                  <v-avatar color="primary" size="38" rounded="lg">
                    <span class="text-caption font-weight-bold text-white">{{ convo.user?.name?.charAt(0) }}</span>
                  </v-avatar>
                </template>
                <v-list-item-title class="text-body-2 font-weight-medium">{{ convo.user?.name }}</v-list-item-title>
                <v-list-item-subtitle class="text-caption text-truncate">{{ convo.last_message }}</v-list-item-subtitle>
                <template #append>
                  <div class="text-right">
                    <div class="text-caption text-medium-emphasis">{{ convo.time }}</div>
                    <v-badge v-if="convo.unread > 0" :content="convo.unread" color="error" inline/>
                  </div>
                </template>
              </v-list-item>
            </v-list>
          </div>
        </v-card>
      </v-col>
      <v-col cols="12" md="8" class="d-flex flex-column" style="height:100%">
        <v-card rounded="xl" flat border class="flex-1 d-flex flex-column overflow-hidden">
          <div v-if="!activeUser" class="flex-1 d-flex flex-column align-center justify-center text-center pa-6">
            <v-icon size="64" color="grey-lighten-2">mdi-message-text-outline</v-icon>
            <div class="text-h6 font-weight-medium mt-4">Select a conversation</div>
            <div class="text-body-2 text-medium-emphasis mt-1">Choose from the left or start a new message</div>
            <v-btn color="primary" variant="tonal" rounded="lg" class="mt-4" prepend-icon="mdi-pencil-plus-outline" @click="newChatDialog = true">Start Conversation</v-btn>
          </div>
          <template v-else>
            <div class="pa-4 d-flex align-center gap-3 border-b">
              <v-avatar color="primary" size="40" rounded="lg">
                <span class="text-body-2 font-weight-bold text-white">{{ activeUser.name?.charAt(0) }}</span>
              </v-avatar>
              <div>
                <div class="text-subtitle-2 font-weight-bold">{{ activeUser.name }}</div>
                <v-chip size="x-small" color="warning" variant="tonal">{{ activeUser.role }}</v-chip>
              </div>
            </div>
            <div ref="messagesContainer" class="flex-1 overflow-y-auto pa-4" style="background:#F8F9FC">
              <div v-if="loadingThread" class="text-center py-8"><v-progress-circular indeterminate color="primary" size="28"/></div>
              <div v-else-if="thread.length === 0" class="text-center py-8">
                <div class="text-body-2 text-medium-emphasis">No messages yet � say hello! ??</div>
              </div>
              <template v-else>
                <template v-for="(msg, i) in thread" :key="msg.id">
                  <div v-if="i === 0 || thread[i-1].date !== msg.date" class="text-center mb-4 mt-2">
                    <v-chip size="x-small" color="grey" variant="tonal">{{ msg.date }}</v-chip>
                  </div>
                  <div class="d-flex mb-3" :class="msg.is_mine ? 'justify-end' : 'justify-start'">
                    <div class="message-bubble px-3 py-2" :class="msg.is_mine ? 'bubble-mine' : 'bubble-theirs'" style="max-width:75%">
                      <div class="text-body-2">{{ msg.body }}</div>
                      <div class="text-right mt-1" style="font-size:10px;opacity:0.7">{{ msg.time }}</div>
                    </div>
                  </div>
                </template>
              </template>
            </div>
            <div class="pa-3 border-t">
              <div class="d-flex gap-2 align-end">
                <v-textarea v-model="newMessage" placeholder="Type a message..." variant="outlined" rounded="lg" rows="1" auto-grow max-rows="4" hide-details class="flex-1" @keydown.enter.prevent="handleEnter"/>
                <v-btn color="primary" icon rounded="lg" size="large" :loading="sending" :disabled="!newMessage.trim()" @click="sendMessage">
                  <v-icon>mdi-send</v-icon>
                </v-btn>
              </div>
              <div class="text-caption text-medium-emphasis mt-1 ml-1">Press Enter to send � Shift+Enter for new line</div>
            </div>
          </template>
        </v-card>
      </v-col>
    </v-row>
    <v-dialog v-model="newChatDialog" max-width="400">
      <v-card rounded="xl" elevation="0" border>
        <v-card-title class="pa-5 pb-2">
          <div class="text-subtitle-1 font-weight-bold">New Message</div>
          <div class="text-caption text-medium-emphasis">Select a staff member to message</div>
        </v-card-title>
        <v-card-text class="pa-5 pt-2">
          <div v-if="contacts.length === 0" class="text-center py-4">
            <div class="text-body-2 text-medium-emphasis">No staff members available</div>
          </div>
          <v-list v-else nav density="compact">
            <v-list-item v-for="contact in contacts" :key="contact.id" rounded="lg" @click="startNewChat(contact)">
              <template #prepend>
                <v-avatar color="primary" size="36" rounded="lg">
                  <span class="text-caption font-weight-bold text-white">{{ contact.name?.charAt(0) }}</span>
                </v-avatar>
              </template>
              <v-list-item-title class="text-body-2 font-weight-medium">{{ contact.name }}</v-list-item-title>
              <v-list-item-subtitle><v-chip size="x-small" color="warning" variant="tonal">{{ contact.role }}</v-chip></v-list-item-subtitle>
            </v-list-item>
          </v-list>
        </v-card-text>
        <v-card-actions class="pa-4 pt-0">
          <v-spacer/>
          <v-btn variant="tonal" rounded="lg" @click="newChatDialog = false">Cancel</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import axios from 'axios'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import { nextTick, onMounted, onUnmounted, ref } from 'vue'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const conversations = ref([])
const thread = ref([])
const contacts = ref([])
const activeUser = ref(null)
const newMessage = ref('')
const loadingConvos = ref(false)
const loadingThread = ref(false)
const sending = ref(false)
const newChatDialog = ref(false)
const messagesContainer = ref(null)
let echo = null

function initEcho() {
  window.Pusher = Pusher
  echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    authEndpoint: '/api/broadcasting/auth',
    auth: { headers: { Authorization: `Bearer ${auth.token}` } },
  })
  echo.private(`messages.${auth.user.id}`)
    .listen('NewMessage', (e) => {
      if (activeUser.value?.id === e.sender_id) {
        thread.value.push({
          id: e.id, body: e.body, sender_id: e.sender_id,
          sender_name: e.sender_name, is_mine: false, time: e.created_at,
          date: new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }),
        })
        scrollToBottom()
      }
      fetchConversations()
    })
}

function scrollToBottom() {
  nextTick(() => { if (messagesContainer.value) messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight })
}

async function fetchConversations() {
  loadingConvos.value = true
  try { const { data } = await axios.get('/api/messages/conversations'); conversations.value = data.conversations }
  catch (e) { console.error(e) }
  finally { loadingConvos.value = false }
}

async function fetchContacts() {
  try { const { data } = await axios.get('/api/messages/contacts'); contacts.value = data.contacts }
  catch (e) { console.error(e) }
}

async function openThread(user) {
  activeUser.value = user
  loadingThread.value = true
  newChatDialog.value = false
  try { const { data } = await axios.get(`/api/messages/thread/${user.id}`); thread.value = data.messages; scrollToBottom() }
  catch (e) { console.error(e) }
  finally { loadingThread.value = false }
}

function startNewChat(contact) { openThread(contact) }

function handleEnter(e) { if (!e.shiftKey) sendMessage() }

async function sendMessage() {
  if (!newMessage.value.trim() || !activeUser.value) return
  sending.value = true
  const body = newMessage.value.trim()
  newMessage.value = ''
  try {
    const { data } = await axios.post('/api/messages/send', { receiver_id: activeUser.value.id, body })
    thread.value.push(data.message)
    scrollToBottom()
    fetchConversations()
  } catch (e) { console.error(e) }
  finally { sending.value = false }
}

onMounted(async () => {
  await Promise.all([fetchConversations(), fetchContacts()])
  initEcho()
})

onUnmounted(() => {
  if (echo) { echo.leave(`messages.${auth.user.id}`); echo.disconnect() }
})
</script>

<style scoped>
.message-bubble { border-radius: 16px; word-break: break-word; }
.bubble-mine { background: #1A237E; color: white; border-bottom-right-radius: 4px; }
.bubble-theirs { background: white; border: 1px solid rgba(0,0,0,0.08); border-bottom-left-radius: 4px; }
.border-b { border-bottom: 1px solid rgba(0,0,0,0.08); }
.border-t { border-top: 1px solid rgba(0,0,0,0.08); }
</style>

