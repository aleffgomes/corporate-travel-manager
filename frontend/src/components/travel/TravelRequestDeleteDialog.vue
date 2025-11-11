<template>
  <Dialog 
    :visible="visible" 
    @update:visible="$emit('update:visible', $event)"
    header="Confirmar Exclusão" 
    :style="{ width: '450px' }" 
    modal
  >
    <div class="flex items-start gap-3 py-4">
      <i class="pi pi-exclamation-triangle text-3xl text-orange-500"></i>
      <div>
        <p class="mb-2">
          Tem certeza que deseja excluir a solicitação de viagem para 
          <strong>{{ request?.destination }}</strong>?
        </p>
        <p class="text-sm text-slate-500">
          Esta ação não pode ser desfeita.
        </p>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end gap-2">
        <Button 
          label="Cancelar" 
          severity="secondary" 
          @click="$emit('update:visible', false)"
          :disabled="submitting"
        />
        <Button 
          label="Excluir" 
          severity="danger"
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
