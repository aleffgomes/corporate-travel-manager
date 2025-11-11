import { ref } from 'vue'

const isDark = ref(false)
const isInitialized = ref(false)

export function useTheme() {
  const toggleTheme = () => {
    isDark.value = !isDark.value
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
    applyTheme()
  }

  const applyTheme = () => {
    const root = document.documentElement
    if (isDark.value) {
      root.classList.add('app-dark')
      root.classList.remove('app-light')
    } else {
      root.classList.remove('app-dark')
      root.classList.add('app-light')
    }
  }

  const initTheme = () => {
    if (isInitialized.value) return
    
    const savedTheme = localStorage.getItem('theme')
    isDark.value = savedTheme === 'dark'
    applyTheme()
    isInitialized.value = true
  }

  return {
    isDark,
    toggleTheme,
    initTheme
  }
}
