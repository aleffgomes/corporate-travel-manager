import axios, { type AxiosInstance, type InternalAxiosRequestConfig, type AxiosError } from 'axios'
import { StorageManager } from '@/utils/storageManager'

const PUBLIC_ROUTES = ['/auth/login', '/auth/register', '/health', '/ping']

const api: AxiosInstance = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

const isPublicRoute = (url?: string): boolean => {
  return PUBLIC_ROUTES.some(route => url?.includes(route))
}

api.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    if (isPublicRoute(config.url)) {
      return config
    }

    const token = StorageManager.getToken()
    if (token && config.headers) {
      config.headers.Authorization = `Bearer ${token}`
    }
    
    return config
  },
  (error: AxiosError) => Promise.reject(error)
)

api.interceptors.response.use(
  (response) => response,
  async (error: AxiosError) => {
    const originalRequest = error.config as InternalAxiosRequestConfig & { _retry?: boolean }
    
    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true
      
      const refreshEvent = new CustomEvent('auth:refresh-needed', { 
        detail: { originalRequest } 
      })
      window.dispatchEvent(refreshEvent)
      
      return new Promise((resolve, reject) => {
        const handleRefreshSuccess = (event: Event) => {
          const customEvent = event as CustomEvent
          const newToken = customEvent.detail.token
          
          if (newToken && originalRequest.headers) {
            originalRequest.headers.Authorization = `Bearer ${newToken}`
            resolve(api(originalRequest))
          } else {
            reject(error)
          }
          
          cleanup()
        }
        
        const handleRefreshFailure = () => {
          reject(error)
          cleanup()
        }
        
        const cleanup = () => {
          window.removeEventListener('auth:refresh-success', handleRefreshSuccess)
          window.removeEventListener('auth:refresh-failed', handleRefreshFailure)
        }
        
        window.addEventListener('auth:refresh-success', handleRefreshSuccess, { once: true })
        window.addEventListener('auth:refresh-failed', handleRefreshFailure, { once: true })
        
        setTimeout(() => {
          cleanup()
          reject(error)
        }, 10000)
      })
    }
    
    return Promise.reject(error)
  }
)

export default api
