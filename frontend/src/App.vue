<template>
  <div id="app" class="min-h-screen">
    <RouterView />
    <Toast />
  </div>
</template>

<script setup lang="ts">
import { RouterView } from 'vue-router'
import Toast from 'primevue/toast'
</script>
