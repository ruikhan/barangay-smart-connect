<template>
  <div class="chatbot-wrapper">
    <transition name="bounce">
      <button v-if="!isOpen" class="chat-toggle" @click="openChat" aria-label="Open chatbot">
        <div class="toggle-icon">
          <svg viewBox="0 0 24 24" fill="none">
            <path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.96 9.96 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z" fill="currentColor"/>
          </svg>
        </div>
        <span class="toggle-pulse"></span>
      </button>
    </transition>

    <transition name="panel-slide">
      <div v-if="isOpen" class="chat-panel">
        <!-- Header -->
        <div class="chat-header">
          <div class="header-left">
            <div class="avatar">
              <svg viewBox="0 0 32 32" fill="none">
                <circle cx="16" cy="16" r="16" fill="#1a3a5c"/>
                <path d="M16 8l2.5 5h5.5l-4.5 3.5 1.5 5.5L16 19l-5 3 1.5-5.5L8 13h5.5L16 8z" fill="#f6c90e"/>
              </svg>
            </div>
            <div class="header-info">
              <h3>YCA Hub Assistant</h3>
              <span class="status-dot"></span>
              <span class="status-text">Online</span>
            </div>
          </div>
          <button class="close-btn" @click="closeChat" aria-label="Close chat">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="18" y1="6" x2="6" y2="18"/>
              <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>

        <!-- Messages -->
        <div class="chat-messages" ref="messagesContainer">
          <div v-if="messages.length === 0" class="welcome-block">
            <div class="welcome-icon">🏛️</div>
            <h4>Kumusta! Maaari ba kitang tulungan?</h4>
            <p>Ask me about barangay services, document requirements, and more.</p>
            <div class="quick-chips">
              <button v-for="chip in quickChips" :key="chip" class="chip" @click="sendQuick(chip)">
                {{ chip }}
              </button>
            </div>
          </div>

          <div v-for="(msg, i) in messages" :key="i" :class="['message-row', msg.role]">
            <div v-if="msg.role === 'assistant'" class="msg-avatar">
              <svg viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="12" fill="#1a3a5c"/>
                <path d="M12 5l1.8 3.8 4.2.6-3 2.9.7 4.2L12 14.4l-3.7 2 .7-4.2-3-2.9 4.2-.6L12 5z" fill="#f6c90e"/>
              </svg>
            </div>
            <div class="bubble-wrap">
              <div class="bubble">{{ msg.content }}</div>
              <span class="msg-time">{{ msg.time }}</span>
            </div>
          </div>

          <div v-if="isTyping" class="message-row assistant">
            <div class="msg-avatar">
              <svg viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="12" fill="#1a3a5c"/>
                <path d="M12 5l1.8 3.8 4.2.6-3 2.9.7 4.2L12 14.4l-3.7 2 .7-4.2-3-2.9 4.2-.6L12 5z" fill="#f6c90e"/>
              </svg>
            </div>
            <div class="bubble-wrap">
              <div class="bubble typing-bubble">
                <span></span><span></span><span></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Input -->
        <div class="chat-input-area">
          <textarea
            ref="inputRef"
            v-model="userInput"
            :disabled="isTyping"
            placeholder="Type your question here..."
            rows="1"
            @keydown.enter.exact.prevent="sendMessage"
            @input="autoResize"
          ></textarea>
          <button class="send-btn" :disabled="!userInput.trim() || isTyping" @click="sendMessage">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="22" y1="2" x2="11" y2="13"/>
              <polygon points="22 2 15 22 11 13 2 9 22 2"/>
            </svg>
          </button>
        </div>
        <p class="chat-footer">Powered by YCA Hub</p>
      </div>
    </transition>
  </div>
</template>

<script setup>
import axios from 'axios'
import { nextTick, ref } from 'vue'

const isOpen          = ref(false)
const userInput       = ref('')
const messages        = ref([])
const isTyping        = ref(false)
const messagesContainer = ref(null)
const inputRef        = ref(null)

const quickChips = [
  'Barangay Clearance',
  'Certificate of Residency',
  'Business Permit',
  'Office Hours',
]

// ── Use token from localStorage (same key as auth store) ──
const getToken = () => localStorage.getItem('token')

const formatTime = () => new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })

const openChat  = () => { isOpen.value = true;  nextTick(() => inputRef.value?.focus()) }
const closeChat = () => { isOpen.value = false }

const scrollToBottom = async () => {
  await nextTick()
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

const autoResize = () => {
  const el = inputRef.value
  if (!el) return
  el.style.height = 'auto'
  el.style.height = Math.min(el.scrollHeight, 120) + 'px'
}

const sendQuick = (text) => { userInput.value = text; sendMessage() }

const sendMessage = async () => {
  const text = userInput.value.trim()
  if (!text || isTyping.value) return

  messages.value.push({ role: 'user', content: text, time: formatTime() })
  userInput.value = ''
  await nextTick()
  if (inputRef.value) inputRef.value.style.height = 'auto'
  await scrollToBottom()

  isTyping.value = true
  await scrollToBottom()

  try {
    const history = messages.value
      .slice(0, -1)
      .map(m => ({ role: m.role, content: m.content }))

    const token = getToken()
    if (!token) throw new Error('Not authenticated')

    const { data } = await axios.post('/api/chatbot', { message: text, history }, {
      headers: { Authorization: `Bearer ${token}` }
    })

    messages.value.push({
      role: 'assistant',
      content: data.reply,
      time: formatTime(),
    })
  } catch (err) {
    const msg = err.response?.status === 401
      ? 'Session expired. Please log in again.'
      : 'Sorry, I am temporarily unavailable. Please try again.'

    messages.value.push({ role: 'assistant', content: msg, time: formatTime() })
  } finally {
    isTyping.value = false
    await scrollToBottom()
    inputRef.value?.focus()
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

* { box-sizing: border-box; margin: 0; padding: 0; }

.chatbot-wrapper {
  position: fixed;
  bottom: 28px;
  right: 28px;
  z-index: 9999;
  font-family: 'Plus Jakarta Sans', sans-serif;
}

.chat-toggle {
  position: relative;
  width: 60px; height: 60px;
  border-radius: 50%;
  border: none;
  background: linear-gradient(135deg, #1a3a5c 0%, #0d5c8f 100%);
  color: #fff;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 6px 24px rgba(13,92,143,0.45);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.chat-toggle:hover { transform: scale(1.08); box-shadow: 0 8px 30px rgba(13,92,143,0.55); }
.toggle-icon svg { width: 28px; height: 28px; }
.toggle-pulse {
  position: absolute; inset: -4px; border-radius: 50%;
  border: 2px solid rgba(13,92,143,0.4);
  animation: pulse 2.2s ease-in-out infinite;
}
@keyframes pulse {
  0%, 100% { transform: scale(1); opacity: 0.6; }
  50% { transform: scale(1.18); opacity: 0; }
}

.chat-panel {
  width: 370px; height: 560px;
  background: #f8f9fc;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(10,30,55,0.18), 0 4px 16px rgba(10,30,55,0.1);
  display: flex; flex-direction: column;
  overflow: hidden;
  border: 1px solid rgba(26,58,92,0.08);
}

.chat-header {
  background: linear-gradient(135deg, #1a3a5c 0%, #0d5c8f 100%);
  padding: 11px 14px;
  display: flex; align-items: center; justify-content: space-between;
  flex-shrink: 0;
}
.header-left { display: flex; align-items: center; gap: 9px; }
.avatar { width: 32px; height: 32px; border-radius: 50%; overflow: hidden; flex-shrink: 0; }
.avatar svg { width: 32px; height: 32px; }
.header-info h3 { color: #fff; font-size: 13px; font-weight: 700; line-height: 1.2; }
.header-info { display: flex; flex-wrap: wrap; align-items: center; gap: 4px; }
.status-dot {
  width: 6px; height: 6px; border-radius: 50%;
  background: #4ade80;
  box-shadow: 0 0 0 2px rgba(74,222,128,0.3);
  animation: blink 2s ease-in-out infinite;
}
@keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
.status-text { color: rgba(255,255,255,0.75); font-size: 11px; font-weight: 500; }
.close-btn {
  background: rgba(255,255,255,0.12); border: none; color: #fff;
  width: 28px; height: 28px; border-radius: 7px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: background 0.2s;
}
.close-btn:hover { background: rgba(255,255,255,0.22); }
.close-btn svg { width: 14px; height: 14px; }

.chat-messages {
  flex: 1; overflow-y: auto;
  padding: 12px 13px;
  display: flex; flex-direction: column; gap: 10px;
  scroll-behavior: smooth;
}
.chat-messages::-webkit-scrollbar { width: 3px; }
.chat-messages::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }

.welcome-block { text-align: center; padding: 8px 6px 2px; animation: fadeUp 0.4s ease; }
.welcome-icon { font-size: 28px; margin-bottom: 7px; }
.welcome-block h4 { font-size: 13px; font-weight: 700; color: #1a3a5c; margin-bottom: 4px; }
.welcome-block p { font-size: 11.5px; color: #6b7280; line-height: 1.5; margin-bottom: 10px; }
.quick-chips { display: flex; flex-wrap: wrap; gap: 5px; justify-content: center; }
.chip {
  background: #fff; border: 1.5px solid #1a3a5c; color: #1a3a5c;
  font-family: inherit; font-size: 11px; font-weight: 600;
  padding: 4px 10px; border-radius: 20px; cursor: pointer;
  transition: all 0.18s ease;
}
.chip:hover { background: #1a3a5c; color: #fff; }

.message-row { display: flex; align-items: flex-end; gap: 6px; animation: fadeUp 0.3s ease; }
.message-row.user { flex-direction: row-reverse; }
.msg-avatar { width: 24px; height: 24px; flex-shrink: 0; }
.msg-avatar svg { width: 24px; height: 24px; border-radius: 50%; }
.bubble-wrap { display: flex; flex-direction: column; max-width: 80%; }
.message-row.user .bubble-wrap { align-items: flex-end; }
.bubble {
  padding: 8px 11px; border-radius: 13px;
  font-size: 12.5px; line-height: 1.5;
  white-space: pre-wrap; word-break: break-word;
}
.message-row.user .bubble {
  background: linear-gradient(135deg, #1a3a5c, #0d5c8f);
  color: #fff; border-bottom-right-radius: 3px;
}
.message-row.assistant .bubble {
  background: #fff; color: #1f2937;
  border-bottom-left-radius: 3px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.07);
}
.msg-time { font-size: 10px; color: #9ca3af; margin-top: 3px; padding: 0 2px; }

.typing-bubble { display: flex; align-items: center; gap: 5px; padding: 14px 18px; }
.typing-bubble span {
  width: 7px; height: 7px; background: #94a3b8; border-radius: 50%;
  animation: typingDot 1.2s ease-in-out infinite;
}
.typing-bubble span:nth-child(2) { animation-delay: 0.2s; }
.typing-bubble span:nth-child(3) { animation-delay: 0.4s; }
@keyframes typingDot {
  0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
  30% { transform: translateY(-6px); opacity: 1; }
}

.chat-input-area {
  padding: 9px 11px; background: #fff;
  border-top: 1px solid #e5e7eb;
  display: flex; align-items: flex-end; gap: 8px; flex-shrink: 0;
}
textarea {
  flex: 1; border: 1.5px solid #e5e7eb; border-radius: 10px;
  padding: 8px 11px; font-family: inherit; font-size: 12.5px;
  color: #1f2937; resize: none; outline: none;
  background: #f8f9fc; transition: border-color 0.2s;
  line-height: 1.5; max-height: 100px; overflow-y: auto;
}
textarea:focus { border-color: #0d5c8f; background: #fff; }
textarea:disabled { opacity: 0.6; cursor: not-allowed; }
textarea::placeholder { color: #9ca3af; font-size: 12px; }
.send-btn {
  width: 34px; height: 34px; border-radius: 10px; border: none;
  background: linear-gradient(135deg, #1a3a5c, #0d5c8f);
  color: #fff; display: flex; align-items: center; justify-content: center;
  cursor: pointer; flex-shrink: 0; transition: opacity 0.2s, transform 0.15s;
}
.send-btn:hover:not(:disabled) { transform: scale(1.05); }
.send-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.send-btn svg { width: 14px; height: 14px; }
.chat-footer {
  text-align: center; font-size: 10px; color: #b0b7c3;
  padding: 4px 0 6px; background: #fff; flex-shrink: 0;
}

.bounce-enter-active { animation: bounceIn 0.4s cubic-bezier(0.34,1.56,0.64,1); }
.bounce-leave-active { animation: bounceOut 0.2s ease; }
@keyframes bounceIn { from { transform: scale(0.5); opacity: 0; } to { transform: scale(1); opacity: 1; } }
@keyframes bounceOut { from { opacity: 1; } to { transform: scale(0.5); opacity: 0; } }

.panel-slide-enter-active { animation: panelIn 0.35s cubic-bezier(0.34,1.4,0.64,1); }
.panel-slide-leave-active { animation: panelOut 0.2s ease; }
@keyframes panelIn { from { transform: scale(0.85) translateY(20px); opacity: 0; transform-origin: bottom right; } to { transform: scale(1) translateY(0); opacity: 1; } }
@keyframes panelOut { from { opacity: 1; } to { opacity: 0; transform: scale(0.9) translateY(10px); } }
@keyframes fadeUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

@media (max-width: 480px) {
  .chatbot-wrapper { bottom: 80px; right: 16px; }
  .chat-toggle { width: 48px; height: 48px; }
  .toggle-icon svg { width: 22px; height: 22px; }
  .chat-panel {
    position: fixed;
    bottom: 148px; right: 12px; left: 12px;
    width: auto; height: 62dvh; max-height: 460px;
    border-radius: 16px;
  }
  .chat-footer { display: none; }
}
</style>