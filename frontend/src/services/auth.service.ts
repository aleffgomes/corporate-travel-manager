import api from './api'
import axios from 'axios'
import type { AxiosResponse } from 'axios'
import type {
  LoginCredentials,
  RegisterData,
  AuthResponse,
  ProfileResponse
} from '@/types'
import { TokenManager } from '@/utils/tokenManager'
import { StorageManager } from '@/utils/storageManager'
import router from '@/router'

let isRefreshing = false
let refreshSubscribers: Array<(token: string | null) => void> = []

class AuthService {
  constructor() {
    this.initializeRefreshListener()
    this.startProactiveRefresh()
  }

  private initializeRefreshListener(): void {
    window.addEventListener('auth:refresh-needed', async () => {
      const newToken = await this.performRefresh()
      
      if (newToken) {
        window.dispatchEvent(new CustomEvent('auth:refresh-success', { 
          detail: { token: newToken } 
        }))
      } else {
        window.dispatchEvent(new CustomEvent('auth:refresh-failed'))
      }
    })
  }

  private startProactiveRefresh(): void {
    setInterval(() => {
      const token = StorageManager.getToken()
      if (token && TokenManager.needsRefresh(token)) {
        this.performRefresh()
      }
    }, 60000)
  }

  private subscribeTokenRefresh(callback: (token: string | null) => void): void {
    refreshSubscribers.push(callback)
  }

  private onTokenRefreshed(token: string | null): void {
    refreshSubscribers.forEach(callback => callback(token))
    refreshSubscribers = []
  }

  private async performRefresh(): Promise<string | null> {
    const currentToken = StorageManager.getToken()
    
    if (!currentToken) {
      return null
    }

    if (isRefreshing) {
      return new Promise((resolve) => {
        this.subscribeTokenRefresh((token: string | null) => resolve(token))
      })
    }

    isRefreshing = true

    try {
      const { data } = await axios.post<AuthResponse>(
        `${import.meta.env.VITE_API_URL}/v1/auth/refresh`,
        {},
        { 
          headers: { 
            Authorization: `Bearer ${currentToken}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          } 
        }
      )

      const newToken = data.data.token
      const user = data.data.user

      StorageManager.setAuth(newToken, user)
      this.onTokenRefreshed(newToken)

      return newToken
    } catch (error) {
      console.error('Token refresh failed:', error)
      this.onTokenRefreshed(null)
      this.handleAuthFailure()
      return null
    } finally {
      isRefreshing = false
    }
  }

  private handleAuthFailure(): void {
    StorageManager.clearAuth()
    
    if (router.currentRoute.value.name !== 'login') {
      router.push({ name: 'login' })
    }
  }

  async login(credentials: LoginCredentials): Promise<AuthResponse> {
    const response: AxiosResponse<AuthResponse> = await api.post('/v1/auth/login', credentials)
    
    if (response.data.data?.token && response.data.data?.user) {
      StorageManager.setAuth(response.data.data.token, response.data.data.user)
    }
    
    return response.data
  }

  async register(userData: RegisterData): Promise<AuthResponse> {
    const response: AxiosResponse<AuthResponse> = await api.post('/v1/auth/register', userData)
    
    if (response.data.data?.token && response.data.data?.user) {
      StorageManager.setAuth(response.data.data.token, response.data.data.user)
    }
    
    return response.data
  }

  async logout(): Promise<void> {
    try {
      await api.post('/v1/auth/logout')
    } catch (error) {
      console.error('Logout API call failed:', error)
    } finally {
      StorageManager.clearAuth()
    }
  }

  async refresh(): Promise<AuthResponse | null> {
    const token = await this.performRefresh()
    
    if (!token) {
      return null
    }

    const user = StorageManager.getUser()
    
    return {
      success: true,
      message: 'Token refreshed successfully',
      data: {
        token,
        user: user!
      }
    }
  }

  async getProfile(): Promise<ProfileResponse> {
    const response: AxiosResponse<ProfileResponse> = await api.get('/v1/auth/me')
    
    if (response.data.data) {
      StorageManager.setUser(response.data.data)
    }
    
    return response.data
  }

  async validateToken(): Promise<boolean> {
    const token = StorageManager.getToken()
    
    if (!token || !TokenManager.isValid(token)) {
      return false
    }

    try {
      await this.getProfile()
      return true
    } catch {
      StorageManager.clearAuth()
      return false
    }
  }
}

export const authService = new AuthService()
