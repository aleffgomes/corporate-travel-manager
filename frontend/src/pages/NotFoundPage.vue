<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="text-center px-4">
      <h1 class="text-9xl font-bold text-primary-600 mb-4">404</h1>
      <h2 class="text-3xl font-semibold text-gray-800 mb-4">
        Página não encontrada
      </h2>
      <p class="text-lg text-gray-600 mb-8">
        A página que você está procurando não existe.
      </p>
      <RouterLink 
        to="/" 
        class="inline-block px-8 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors duration-200"
      >
        Voltar para o início
      </RouterLink>
    </div>
  </div>
</template>

<script setup lang="ts">
import { RouterLink } from 'vue-router'
</script>
