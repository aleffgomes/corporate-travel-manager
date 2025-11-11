import type { User } from '@/types'

const STORAGE_KEYS = {
  TOKEN: 'token',
  USER: 'user'
} as const

export class StorageManager {
  static setToken(token: string): void {
    try {
      localStorage.setItem(STORAGE_KEYS.TOKEN, token)
    } catch (error) {
      console.error('Failed to save token:', error)
    }
  }

  static getToken(): string | null {
    try {
      return localStorage.getItem(STORAGE_KEYS.TOKEN)
    } catch (error) {
      console.error('Failed to get token:', error)
      return null
    }
  }

  static removeToken(): void {
    try {
      localStorage.removeItem(STORAGE_KEYS.TOKEN)
    } catch (error) {
      console.error('Failed to remove token:', error)
    }
  }

  static setUser(user: User): void {
    try {
      localStorage.setItem(STORAGE_KEYS.USER, JSON.stringify(user))
    } catch (error) {
      console.error('Failed to save user:', error)
    }
  }

  static getUser(): User | null {
    try {
      const userJson = localStorage.getItem(STORAGE_KEYS.USER)
      return userJson ? JSON.parse(userJson) : null
    } catch (error) {
      console.error('Failed to get user:', error)
      return null
    }
  }

  static removeUser(): void {
    try {
      localStorage.removeItem(STORAGE_KEYS.USER)
    } catch (error) {
      console.error('Failed to remove user:', error)
    }
  }

  static clearAuth(): void {
    this.removeToken()
    this.removeUser()
  }

  static setAuth(token: string, user: User): void {
    this.setToken(token)
    this.setUser(user)
  }

  static getAuth(): { token: string | null; user: User | null } {
    return {
      token: this.getToken(),
      user: this.getUser()
    }
  }
}
