export interface User {
  id: number
  name: string
  email: string
  role: string
  created_at: string
  updated_at: string
}

export interface LoginCredentials {
  email: string
  password: string
}

export interface RegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export interface AuthResponse {
  success: boolean
  message: string
  data: {
    token: string
    user: User
  }
}

export interface ProfileResponse {
  success: boolean
  data: User
}

export interface AuthState {
  user: User | null
  token: string | null
  loading: boolean
  error: string | null
}
