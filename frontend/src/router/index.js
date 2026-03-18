import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

// ← REMOVED: import MainLayout from '../layouts/MainLayout.vue'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/LoginView.vue'),
    meta: { guest: true },
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('../views/RegisterView.vue'),
    meta: { guest: true },
  },
  {
    path: '/',
    component: () => import('../layouts/MainLayout.vue'),  // ← lazy load
    meta: { requiresAuth: true },
    children: [
      { path: '',           redirect: '/dashboard' },
      { path: 'dashboard',     name: 'Dashboard',     component: () => import('../views/DashboardView.vue')     },
      { path: 'services',      name: 'Services',      component: () => import('../views/ServicesView.vue')      },
      { path: 'requests',      name: 'Requests',      component: () => import('../views/RequestsView.vue')      },
      { path: 'announcements', name: 'Announcements', component: () => import('../views/AnnouncementsView.vue') },
      { path: 'messages',      name: 'Messages',      component: () => import('../views/MessagesView.vue')      },
      { path: 'map',           name: 'Map',           component: () => import('../views/MapView.vue')           },
      { path: 'admin',         name: 'Admin',         component: () => import('../views/AdminView.vue')         },
      { path: 'profile',       name: 'Profile',       component: () => import('../views/ProfileView.vue')       },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const auth = useAuthStore()
  if (to.meta.requiresAuth && !auth.token) return next('/login')
  if (to.meta.guest && auth.token) return next('/dashboard')
  next()
})

export default router