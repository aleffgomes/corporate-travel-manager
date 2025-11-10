import api from './api'
import type { AxiosResponse } from 'axios'
import type {
  LoginCredentials,
  RegisterData,
  AuthResponse,
  ProfileResponse
} from '@/types'

export const authService = {
  async login(credentials: LoginCredentials): Promise<AuthResponse> {
    const response: AxiosResponse<AuthResponse> = await api.post('/v1/auth/login', credentials)
    return response.data
  },

  async register(userData: RegisterData): Promise<AuthResponse> {
    const response: AxiosResponse<AuthResponse> = await api.post('/v1/auth/register', userData)
    return response.data
  },

  async logout(): Promise<void> {
    await api.post('/v1/auth/logout')
  },

  async refresh(): Promise<AuthResponse> {
    const response: AxiosResponse<AuthResponse> = await api.post('/v1/auth/refresh')
    return response.data
  },

  async getProfile(): Promise<ProfileResponse> {
    const response: AxiosResponse<ProfileResponse> = await api.get('/v1/auth/me')
    return response.data
  }
}
