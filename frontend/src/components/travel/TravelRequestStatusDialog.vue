<template>
  <Dialog 
    :visible="visible" 
    @update:visible="$emit('update:visible', $event)"
    :header="action === 'approve' ? 'Aprovar Solicitação' : 'Rejeitar Solicitação'" 
    :style="{ width: '500px' }" 
    modal
  >
    <div class="space-y-4 py-4">
      <p v-if="action === 'approve'">
        Tem certeza que deseja aprovar a solicitação de viagem para 
        <strong>{{ request?.destination }}</strong>?
      </p>

      <template v-else>
        <p class="mb-3">
          Rejeitar solicitação de viagem para <strong>{{ request?.destination }}</strong>
        </p>
        
        <div class="flex flex-col gap-2">
          <label for="rejection_reason" class="font-semibold">Motivo da Rejeição *</label>
          <Textarea 
            id="rejection_reason" 
            v-model="rejectionReason" 
            rows="3" 
            placeholder="Descreva o motivo da rejeição..."
            :disabled="submitting"
          />
        </div>
      </template>
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
          :label="action === 'approve' ? 'Aprovar' : 'Rejeitar'" 
          :severity="action === 'approve' ? 'success' : 'danger'"
          @click="handleConfirm"
          :loading="submitting"
        />
      </div>
    </template>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import type { TravelRequest } from '@/types'
import Dialog from 'primevue/dialog'
import Textarea from 'primevue/textarea'
import Button from 'primevue/button'

interface Props {
  visible: boolean
  request: TravelRequest | null
  action: 'approve' | 'reject'
  submitting?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  submitting: false
})

const emit = defineEmits<{
  'update:visible': [value: boolean]
  'confirm': [rejectionReason?: string]
}>()

const rejectionReason = ref('')

const handleConfirm = () => {
  if (props.action === 'reject' && !rejectionReason.value.trim()) {
    return
  }
  
  emit('confirm', props.action === 'reject' ? rejectionReason.value : undefined)
}

watch(() => props.visible, (newVal) => {
  if (!newVal) {
    rejectionReason.value = ''
  }
})
</script>
