<template>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <h1 class="text-2xl font-bold text-primary-600">
            Corporate Travel Manager
          </h1>
          <div class="flex items-center gap-4">
            <span class="text-gray-700">Olá, {{ authStore.user?.name }}</span>
            <button @click="handleLogout" class="btn-danger">
              Sair
            </button>
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="card text-center max-w-2xl mx-auto">
        <h2 class="text-3xl font-bold text-gray-800 mb-4">
          Bem-vindo
        </h2>
        <p class="text-gray-600 text-lg">
          Página principal
        </p>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

async function handleLogout(): Promise<void> {
  await authStore.logout()
  router.push({ name: 'login' })
}
</script>
