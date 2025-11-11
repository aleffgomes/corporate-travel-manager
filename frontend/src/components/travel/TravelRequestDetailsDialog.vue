<template>
  <Dialog 
    :visible="visible" 
    @update:visible="$emit('update:visible', $event)"
    header="Detalhes da Solicitação" 
    :style="{ width: '600px' }" 
    modal
  >
    <div v-if="request" class="space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <p class="text-sm text-slate-500 mb-1">Destino</p>
          <p class="font-semibold">{{ request.destination }}</p>
        </div>
        
        <div>
          <p class="text-sm text-slate-500 mb-1">Status</p>
          <Tag :value="getStatusLabel(request.status)" :severity="getStatusSeverity(request.status)" />
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <p class="text-sm text-slate-500 mb-1">Data de Início</p>
          <p class="font-semibold">{{ formatDate(request.start_date) }}</p>
        </div>
        
        <div>
          <p class="text-sm text-slate-500 mb-1">Data de Término</p>
          <p class="font-semibold">{{ formatDate(request.end_date) }}</p>
        </div>
      </div>

      <div>
        <p class="text-sm text-slate-500 mb-1">Motivo</p>
        <p>{{ request.reason }}</p>
      </div>

      <div v-if="request.estimated_cost">
        <p class="text-sm text-slate-500 mb-1">Custo Estimado</p>
        <p class="font-semibold">{{ formatCurrency(request.estimated_cost) }}</p>
      </div>

      <div v-if="request.user" class="pt-3 border-t">
        <p class="text-sm text-slate-500 mb-1">Solicitante</p>
        <p class="font-semibold">{{ request.user.name }}</p>
        <p class="text-sm text-slate-500">{{ request.user.email }}</p>
      </div>

      <div v-if="request.rejection_reason" class="pt-3 border-t">
        <p class="text-sm text-slate-500 mb-1">Motivo da Rejeição</p>
        <p class="text-red-600">{{ request.rejection_reason }}</p>
      </div>

      <div class="pt-3 border-t text-sm text-slate-500">
        <p>Criado em: {{ formatDateTime(request.created_at) }}</p>
        <p>Atualizado em: {{ formatDateTime(request.updated_at) }}</p>
      </div>
    </div>

    <template #footer>
      <Button label="Fechar" @click="$emit('update:visible', false)" />
    </template>
  </Dialog>
</template>

<script setup lang="ts">
import type { TravelRequest } from '@/types'
import { formatDate, formatDateTime, formatCurrency } from '@/utils/formatters'
import Dialog from 'primevue/dialog'
import Tag from 'primevue/tag'
import Button from 'primevue/button'

interface Props {
  visible: boolean
  request: TravelRequest | null
}

defineProps<Props>()

defineEmits<{
  'update:visible': [value: boolean]
}>()

const getStatusLabel = (status: string) => {
  const labels: Record<string, string> = {
    pending: 'Pendente',
    approved: 'Aprovado',
    rejected: 'Rejeitado',
    cancelled: 'Cancelado'
  }
  return labels[status] || status
}

const getStatusSeverity = (status: string) => {
  const severities: Record<string, any> = {
    pending: 'warning',
    approved: 'success',
    rejected: 'danger',
    cancelled: 'secondary'
  }
  return severities[status] || 'info'
}
</script>
