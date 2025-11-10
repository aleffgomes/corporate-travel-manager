<template>
  <div class="min-h-screen flex items-center justify-center px-4">
    <div class="card max-w-md w-full">
      <h1 class="text-3xl font-bold text-center mb-8 text-gray-800">Cadastro</h1>
      
      <form @submit.prevent="handleRegister" class="space-y-5">
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
            Nome
          </label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            required
            placeholder="Seu nome"
            class="input-field"
          />
        </div>

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
            minlength="6"
            class="input-field"
          />
          <div v-if="form.password" class="mt-2 space-y-1">
            <p class="text-xs" :class="hasMinLength ? 'text-green-600' : 'text-red-600'">
              {{ hasMinLength ? '' : 'Mínimo 6 caracteres' }} 
            </p>
            <p class="text-xs" :class="hasLetters ? 'text-green-600' : 'text-red-600'">
              {{ hasLetters ? '' : 'Deve conter letras' }} 
            </p>
            <p class="text-xs" :class="hasNumbers ? 'text-green-600' : 'text-red-600'">
              {{ hasNumbers ? '' : 'Deve conter números' }} 
            </p>
          </div>
        </div>

        <div>
          <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
            Confirmar Senha
          </label>
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            required
            placeholder="••••••••"
            class="input-field"
          />
          <p v-if="form.password_confirmation && !passwordsMatch" class="mt-2 text-xs text-red-600">
            Senhas não conferem
          </p>
        </div>

        <div v-if="authStore.error" class="error-message">
          {{ authStore.error }}
        </div>

        <button type="submit" :disabled="authStore.loading" class="btn-primary">
          {{ authStore.loading ? 'Cadastrando...' : 'Cadastrar' }}
        </button>
      </form>

      <p class="text-center mt-6 text-gray-600">
        Já tem conta? 
        <RouterLink to="/login" class="text-primary-600 hover:text-primary-700 font-medium">
          Entrar
        </RouterLink>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, computed } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import type { RegisterData } from '@/types'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive<RegisterData>({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const hasMinLength = computed(() => form.password.length >= 6)
const hasLetters = computed(() => /[a-zA-Z]/.test(form.password))
const hasNumbers = computed(() => /\d/.test(form.password))
const isPasswordValid = computed(() => hasMinLength.value && hasLetters.value && hasNumbers.value)
const passwordsMatch = computed(() => form.password === form.password_confirmation)

const isFormValid = computed(() => 
  form.name.trim() !== '' &&
  form.email.trim() !== '' &&
  isPasswordValid.value &&
  passwordsMatch.value
)

async function handleRegister() {
  if (!isFormValid.value) {
    authStore.error = 'Verifique os dados e tente novamente.'
    return
  }

  const success = await authStore.register(form)
  if (success) {
    router.push({ name: 'home' })
  }
}
</script>
