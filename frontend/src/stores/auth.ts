import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authService } from '@/services/auth.service'
import { StorageManager } from '@/utils/storageManager'
import type { User, LoginCredentials, RegisterData } from '@/types'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(StorageManager.getToken())
  const user = ref<User | null>(StorageManager.getUser())
  const loading = ref<boolean>(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => !!token.value && !!user.value)

  async function login(credentials: LoginCredentials): Promise<boolean> {
    loading.value = true
    error.value = null
    
    try {
      const response = await authService.login(credentials)
      
      token.value = response.data.token
      user.value = response.data.user
      
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
      loading.value = false
    }
  }

  async function refreshProfile(): Promise<void> {
    try {
      const response = await authService.getProfile()
      user.value = response.data
    } catch (err) {
      console.error('Failed to refresh profile:', err)
    }
  }

  async function validateToken(): Promise<boolean> {
    if (!token.value) {
      return false
    }

    try {
      const isValid = await authService.validateToken()
      
      if (isValid) {
        user.value = StorageManager.getUser()
        return true
      } else {
        token.value = null
        user.value = null
        return false
      }
    } catch {
      token.value = null
      user.value = null
      return false
    }
  }

  async function refreshToken(): Promise<boolean> {
    if (!token.value) {
      return false
    }

    loading.value = true
    
    try {
      const response = await authService.refresh()
      
      if (response) {
        token.value = response.data.token
        user.value = response.data.user
        return true
      }
      
      return false
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Token refresh failed'
      token.value = null
      user.value = null
      return false
    } finally {
      loading.value = false
    }
  }

  function syncWithStorage(): void {
    token.value = StorageManager.getToken()
    user.value = StorageManager.getUser()
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
    refreshProfile,
    validateToken,
    refreshToken,
    syncWithStorage
  }
})
