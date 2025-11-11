<template>
  <div class="min-h-screen">
    <Menubar :model="menuItems" class="border-b shadow-sm" style="border-radius: 0 !important;">
      <template #start>
        <img src="/logo.png" alt="CTM Logo" class="h-8 mr-3" />
      </template>
      <template #end>
        <div class="flex items-center gap-3">
          <Button 
            :icon="isDark ? 'pi pi-sun' : 'pi pi-moon'" 
            text 
            rounded 
            @click="toggleTheme"
            v-tooltip.bottom="isDark ? 'Modo Claro' : 'Modo Escuro'"
          />
          <Divider layout="vertical" class="h-6" />
          <span class="text-sm font-medium">{{ userName }}</span>
          <Button 
            icon="pi pi-sign-out" 
            text 
            rounded 
            @click="handleLogout" 
            severity="danger"
            v-tooltip.bottom="'Sair'"
          />
        </div>
      </template>
    </Menubar>
    
    <div class="p-6 max-w-screen-2xl mx-auto">
      <router-view />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useTheme } from '@/composables/useTheme'
import Menubar from 'primevue/menubar'
import Button from 'primevue/button'
import Divider from 'primevue/divider'

const router = useRouter()
const authStore = useAuthStore()
const { isDark, toggleTheme } = useTheme()

const userName = computed(() => authStore.user?.name || 'Usuário')
const isAdmin = computed(() => authStore.user?.role === 'admin')

const menuItems = computed(() => {
  const items = [
    {
      label: 'Dashboard',
      icon: 'pi pi-home',
      command: () => router.push('/')
    }
  ]

  if (isAdmin.value) {
    items.push({
      label: 'Solicitações',
      icon: 'pi pi-list',
      command: () => router.push('/travel-requests')
    })
  }

  return items
})

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>
