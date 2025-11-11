<template>
  <Dialog 
    :visible="visible" 
    @update:visible="$emit('update:visible', $event)"
    header="Cancelar Solicitação" 
    :style="{ width: '500px' }" 
    modal
  >
    <div class="py-4">
      <p>
        Tem certeza que deseja cancelar a solicitação de viagem para 
        <strong>{{ request?.destination }}</strong>?
      </p>
      <p class="text-slate-500 text-sm mt-3">
        Esta ação não poderá ser desfeita.
      </p>
    </div>

    <template #footer>
      <div class="flex justify-end gap-2">
        <Button 
          label="Não" 
          severity="secondary" 
          @click="$emit('update:visible', false)"
          :disabled="submitting"
        />
        <Button 
          label="Sim, Cancelar" 
          severity="warning"
          @click="$emit('confirm')"
          :loading="submitting"
        />
      </div>
    </template>
  </Dialog>
</template>

<script setup lang="ts">
import type { TravelRequest } from '@/types'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'

interface Props {
  visible: boolean
  request: TravelRequest | null
  submitting?: boolean
}

withDefaults(defineProps<Props>(), {
  submitting: false
})

defineEmits<{
  'update:visible': [value: boolean]
  'confirm': []
}>()
</script>
