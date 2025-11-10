<template>
  <div class="min-h-screen flex items-center justify-center px-4">
    <div class="card max-w-md w-full">
      <h1 class="text-3xl font-bold text-center mb-8 text-gray-800">Login</h1>
      
      <form @submit.prevent="handleLogin" class="space-y-5">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
            Email
          </label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            placeholder="seu@email.com"
            class="input-field"
          />
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
            Senha
          </label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            required
            placeholder="••••••••"
            class="input-field"
          />
        </div>

        <div v-if="authStore.error" class="error-message">
          {{ authStore.error }}
        </div>

        <button type="submit" :disabled="authStore.loading" class="btn-primary">
          {{ authStore.loading ? 'Entrando...' : 'Entrar' }}
        </button>
      </form>

      <p class="text-center mt-6 text-gray-600">
        Não tem conta? 
        <RouterLink to="/register" class="text-primary-600 hover:text-primary-700 font-medium">
          Cadastre-se
        </RouterLink>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import type { LoginCredentials } from '@/types'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive<LoginCredentials>({
  email: '',
  password: ''
})

async function handleLogin() {
  const success = await authStore.login(form)
  if (success) {
    router.push({ name: 'home' })
  }
}
</script>
