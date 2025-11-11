const REFRESH_THRESHOLD_MS = 5 * 60 * 1000

interface TokenPayload {
  exp: number
  iat: number
  sub?: string
  [key: string]: any
}

export class TokenManager {
  static decode(token: string): TokenPayload | null {
    try {
      const payload = token.split('.')[1]
      if (!payload) return null
      
      const decoded = JSON.parse(atob(payload))
      return decoded
    } catch (error) {
      console.error('Failed to decode token:', error)
      return null
    }
  }

  static getExpiration(token: string): number | null {
    const payload = this.decode(token)
    return payload?.exp ? payload.exp * 1000 : null
  }

  static isExpired(token: string): boolean {
    const exp = this.getExpiration(token)
    if (!exp) return true
    return Date.now() >= exp
  }

  static isExpiringSoon(token: string, thresholdMs: number = REFRESH_THRESHOLD_MS): boolean {
    const exp = this.getExpiration(token)
    if (!exp) return true
    
    const timeRemaining = exp - Date.now()
    return timeRemaining < thresholdMs && timeRemaining > 0
  }

  static isValid(token: string): boolean {
    if (!token || token.split('.').length !== 3) {
      return false
    }
    return !this.isExpired(token)
  }

  static getTimeUntilExpiration(token: string): number {
    const exp = this.getExpiration(token)
    if (!exp) return 0
    return Math.max(0, exp - Date.now())
  }

  static needsRefresh(token: string): boolean {
    return this.isValid(token) && this.isExpiringSoon(token)
  }
}
