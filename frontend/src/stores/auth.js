import axios from 'axios'
import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  const user  = ref(JSON.parse(localStorage.getItem('user')  || 'null'))
  const token = ref(localStorage.getItem('token') || null)

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin    = computed(() => user.value?.role === 'admin')
  const isStaff    = computed(() => user.value?.role === 'staff')
  const isResident = computed(() => user.value?.role === 'resident')

  function setAuth(userData, userToken) {
    user.value  = userData
    token.value = userToken
    localStorage.setItem('user',  JSON.stringify(userData))
    localStorage.setItem('token', userToken)
    axios.defaults.headers.common['Authorization'] = `Bearer ${userToken}`
  }

  function clearAuth() {
    user.value  = null
    token.value = null
    localStorage.removeItem('user')
    localStorage.removeItem('token')
    delete axios.defaults.headers.common['Authorization']
  }

  async function login(email, password) {
    const { data } = await axios.post('/api/auth/login', { email, password })
    setAuth(data.user, data.token)
    return data
  }

  async function register(payload) {
    const { data } = await axios.post('/api/auth/register', payload)
    setAuth(data.user, data.token)
    return data
  }

async function logout() {
  try {
    await axios.post('/api/auth/logout')
  } catch {
    // token already expired — just clear locally
  } finally {
    clearAuth()
  }
}

  async function fetchMe() {
    const { data } = await axios.get('/api/auth/me')
    user.value = data.user
    localStorage.setItem('user', JSON.stringify(data.user))
  }

  if (token.value) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
  }

  return {
    user, token, isAuthenticated,
    isAdmin, isStaff, isResident,
    login, register, logout, fetchMe, clearAuth,
  }
})