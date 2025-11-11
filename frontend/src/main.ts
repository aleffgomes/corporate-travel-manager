import { createApp } from 'vue'
import { createPinia } from 'pinia'
import PrimeVue from 'primevue/config'
import ToastService from 'primevue/toastservice'
import Tooltip from 'primevue/tooltip'
import { definePreset } from '@primevue/themes'
import Aura from '@primevue/themes/aura'
import { primary, surface } from './config/colors'
import { useTheme } from './composables/useTheme'
import App from './App.vue'
import router from './router'
import 'primeicons/primeicons.css'
import './assets/main.css'

const { initTheme } = useTheme()
initTheme()

const NavyPreset = definePreset(Aura, {
  semantic: {
    primary: primary.primary,
    colorScheme: {
      light: {
        surface
      },
      dark: {
        surface
      }
    }
  }
})

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(PrimeVue, {
  theme: {
    preset: NavyPreset,
    options: {
      darkModeSelector: '.app-dark',
      cssLayer: false
    }
  }
})
app.use(ToastService)
app.directive('tooltip', Tooltip)

app.mount('#app')
