import { createRouter, createWebHistory } from 'vue-router'
import type { RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppLayout from '@/layouts/AppLayout.vue'

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    component: AppLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'home',
        component: () => import('@/pages/DashboardPage.vue')
      },
      {
        path: '/travel-requests',
        name: 'travel-requests',
        component: () => import('@/pages/TravelRequestsPage.vue'),
        meta: { requiresAuth: true, requiresAdmin: true }
      }
    ]
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('@/pages/LoginPage.vue'),
    meta: { guest: true }
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('@/pages/RegisterPage.vue'),
    meta: { guest: true }
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/pages/NotFoundPage.vue')
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

let isValidatingToken = false

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  if (authStore.isAuthenticated && !isValidatingToken) {
    isValidatingToken = true
    const isValid = await authStore.validateToken()
    isValidatingToken = false
    
    if (!isValid && to.meta.requiresAuth) {
      return next({ name: 'login' })
    }
  }
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login' })
  } else if (to.meta.requiresAdmin && authStore.user?.role !== 'admin') {
    next({ name: 'home' })
  } else if (to.meta.guest && authStore.isAuthenticated) {
    next({ name: 'home' })
  } else {
    next()
  }
})

export default router
