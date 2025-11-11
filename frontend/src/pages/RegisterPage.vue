<template>
  <div class="min-h-screen flex items-center justify-center bg-slate-100 dark:bg-slate-900 px-4 py-8">
    
    <Card class="w-full max-w-md shadow-2xl">
      <template #header>
        <div class="text-center pt-6 px-6">
          <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-100 dark:bg-primary-900/50 mb-4">
            <i class="pi pi-user-plus text-3xl text-primary-700 dark:text-primary-100"></i>
          </div>
          <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-500">Criar Conta</h1>
          <p class="text-slate-600 dark:text-slate-400 mt-2 text-base">Preencha seus dados para começar</p>
        </div>
      </template>
      
      <template #content>
        <form @submit.prevent="handleRegister" class="space-y-4 px-2">
          <div class="flex flex-col gap-2">
            <label for="name" class="font-semibold text-slate-800 dark:text-slate-500 text-sm">Nome Completo</label>
            <InputText 
              id="name" 
              v-model="form.name" 
              placeholder="Digite seu nome"
              :invalid="!!error"
              size="large"
              required
            />
          </div>

          <div class="flex flex-col gap-2">
            <label for="email" class="font-semibold text-slate-800 dark:text-slate-500 text-sm">Email</label>
            <InputText 
              id="email" 
              v-model="form.email" 
              type="email" 
              placeholder="seu@email.com"
              :invalid="!!error"
              size="large"
              required
            />
          </div>

          <div class="flex flex-col gap-2">
            <label for="password" class="font-semibold text-slate-800 dark:text-slate-500 text-sm">Senha</label>
            <Password 
              id="password" 
              v-model="form.password" 
              placeholder="Crie uma senha"
              toggleMask
              :invalid="!!error"
              inputClass="w-full"
              required
            >
              <template #footer>
                <Divider />
                <p class="text-sm font-semibold mb-2 text-slate-800 dark:text-slate-500">Requisitos:</p>
                <ul class="pl-2 ml-2 text-sm space-y-1">
                  <li :class="hasMinLength ? 'text-primary-600 dark:text-primary-400' : 'text-slate-600 dark:text-slate-400'">
                    <i :class="hasMinLength ? 'pi pi-check' : 'pi pi-times'"></i>
                    Mínimo 6 caracteres
                  </li>
                  <li :class="hasLetters ? 'text-primary-600 dark:text-primary-400' : 'text-slate-600 dark:text-slate-400'">
                    <i :class="hasLetters ? 'pi pi-check' : 'pi pi-times'"></i>
                    Conter letras
                  </li>
                  <li :class="hasNumbers ? 'text-primary-600 dark:text-primary-400' : 'text-slate-600 dark:text-slate-400'">
                    <i :class="hasNumbers ? 'pi pi-check' : 'pi pi-times'"></i>
                    Conter números
                  </li>
                </ul>
              </template>
            </Password>
          </div>

          <div class="flex flex-col gap-2">
            <label for="password_confirmation" class="font-semibold text-slate-800 dark:text-slate-500 text-sm">Confirmar Senha</label>
            <Password 
              id="password_confirmation" 
              v-model="form.password_confirmation" 
              placeholder="Digite a senha novamente"
              :feedback="false"
              toggleMask
              :invalid="!!(form.password_confirmation && !passwordsMatch)"
              inputClass="w-full"
              required
            />
            <small v-if="form.password_confirmation && !passwordsMatch" class="text-red-600 font-medium">
              As senhas não conferem
            </small>
          </div>

          <Message v-if="error" severity="error" :closable="false" class="text-sm">{{ error }}</Message>

          <Button 
            type="submit" 
            label="Cadastrar" 
            icon="pi pi-user-plus" 
            :loading="loading"
            :disabled="!isFormValid"
            size="large"
            class="w-full mt-6"
          />
        </form>
      </template>

      <template #footer>
        <div class="text-center text-sm text-slate-600 dark:text-slate-400 pb-2">
          Já tem conta? 
          <router-link to="/login" class="text-primary-700 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300 font-semibold">
            Entrar
          </router-link>
        </div>
      </template>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { reactive, computed, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useTheme } from '@/composables/useTheme'
import type { RegisterData } from '@/types'
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Button from 'primevue/button'
import Message from 'primevue/message'
import Divider from 'primevue/divider'

const router = useRouter()
const authStore = useAuthStore()
const { isDark, toggleTheme } = useTheme()
const loading = ref(false)
const error = ref('')

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
    error.value = 'Verifique os dados e tente novamente.'
    return
  }

  loading.value = true
  error.value = ''
  
  const success = await authStore.register(form)
  
  if (success) {
    router.push({ name: 'home' })
  } else {
    error.value = authStore.error || 'Erro ao criar conta'
  }
  
  loading.value = false
}
</script>
