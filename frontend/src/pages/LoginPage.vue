<template>
  <div class="min-h-screen flex items-center justify-center bg-slate-100 dark:bg-slate-900 px-4">
    <Card class="w-full max-w-md shadow-2xl bg-white dark:bg-slate-800">
      <template #header>
        <div class="text-center pt-6 px-6">
          <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-100 dark:bg-primary-900/50 mb-4">
            <i class="pi pi-briefcase text-3xl text-primary-700 dark:text-primary-100"></i>
          </div>
          <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-500">Bem-vindo</h1>
          <p class="text-slate-600 dark:text-slate-400 mt-2 text-base">Entre na sua conta para continuar</p>
        </div>
      </template>
      
      <template #content>
        <form @submit.prevent="handleLogin" class="space-y-5 px-2">
          <div class="flex flex-col gap-2">
            <label for="email" class="font-semibold text-slate-800 dark:text-slate-500 text-sm">Email</label>
            <InputText 
              id="email" 
              v-model="form.email" 
              type="email" 
              placeholder="seu@email.com"
              :invalid="!!error"
              class="w-full"
              size="large"
              required
            />
          </div>

          <div class="flex flex-col gap-2">
            <label for="password" class="font-semibold text-slate-800 dark:text-slate-500 text-sm">Senha</label>
            <Password 
              id="password" 
              v-model="form.password" 
              placeholder="Digite sua senha"
              :feedback="false"
              toggleMask
              :invalid="!!error"
              class="w-full"
              inputClass="w-full"
              required
            />
          </div>

          <Message v-if="error" severity="error" :closable="false" class="text-sm">{{ error }}</Message>

          <Button 
            type="submit" 
            label="Entrar" 
            icon="pi pi-sign-in" 
            :loading="loading"
            size="large"
            class="w-full mt-6"
          />
        </form>
      </template>

      <template #footer>
        <div class="text-center text-sm text-slate-600 pb-2">
          Não tem conta? 
          <router-link to="/register" class="text-primary-700 hover:text-primary-800 font-semibold">
            Cadastre-se
          </router-link>
        </div>
      </template>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import type { LoginCredentials } from '@/types'
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Button from 'primevue/button'
import Message from 'primevue/message'

const router = useRouter()
const authStore = useAuthStore()
const loading = ref(false)
const error = ref('')

const form = reactive<LoginCredentials>({
  email: '',
  password: ''
})

async function handleLogin() {
  loading.value = true
  error.value = ''
  
  const success = await authStore.login(form)
  
  if (success) {
    router.push({ name: 'home' })
  } else {
    error.value = authStore.error || 'Erro ao fazer login'
  }
  
  loading.value = false
}
</script>
