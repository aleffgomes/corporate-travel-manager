import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authService } from '@/services/auth.service'
import type { User, LoginCredentials, RegisterData } from '@/types'

const TOKEN_KEY = 'token'
const USER_KEY = 'user'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem(TOKEN_KEY) || null)
  const user = ref<User | null>(JSON.parse(localStorage.getItem(USER_KEY) || 'null'))
  const loading = ref<boolean>(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => !!token.value)

  async function login(credentials: LoginCredentials): Promise<boolean> {
    loading.value = true
    error.value = null
    
    try {
      const response = await authService.login(credentials)
      token.value = response.data.token
      user.value = response.data.user
      
      localStorage.setItem(TOKEN_KEY, token.value)
      localStorage.setItem(USER_KEY, JSON.stringify(user.value))
      
      return true
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Login failed'
      return false
    } finally {
      loading.value = false
    }
  }

  async function register(userData: RegisterData): Promise<boolean> {
    loading.value = true
    error.value = null
    
    try {
      const response = await authService.register(userData)
      token.value = response.data.token
      user.value = response.data.user
      
      localStorage.setItem(TOKEN_KEY, token.value)
      localStorage.setItem(USER_KEY, JSON.stringify(user.value))
      
      return true
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Registration failed'
      return false
    } finally {
      loading.value = false
    }
  }

  async function logout(): Promise<void> {
    loading.value = true
    
    try {
      await authService.logout()
    } catch (err) {
      console.error('Logout error:', err)
    } finally {
      token.value = null
      user.value = null
      localStorage.removeItem(TOKEN_KEY)
      localStorage.removeItem(USER_KEY)
      loading.value = false
    }
  }

  async function refreshProfile(): Promise<void> {
    try {
      const response = await authService.getProfile()
      user.value = response.data
      localStorage.setItem(USER_KEY, JSON.stringify(user.value))
    } catch (err) {
      console.error('Failed to refresh profile:', err)
    }
  }

  return {
    token,
    user,
    loading,
    error,
    isAuthenticated,
    login,
    register,
    logout,
    refreshProfile
  }
})
