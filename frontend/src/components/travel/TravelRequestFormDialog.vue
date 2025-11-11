<template>
  <Dialog 
    :visible="visible" 
    @update:visible="$emit('update:visible', $event)"
    :header="isEdit ? 'Editar Solicitação' : 'Nova Solicitação de Viagem'" 
    :style="{ width: '600px' }" 
    modal
  >
    <form @submit.prevent="handleSubmit" class="space-y-4 mt-4">
      <div class="flex flex-col gap-2">
        <label for="destination" class="font-semibold">Destino *</label>
        <InputText id="destination" v-model="form.destination" required :disabled="submitting" />
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div class="flex flex-col gap-2">
          <label for="start_date" class="font-semibold">Data de Início *</label>
          <Calendar 
            id="start_date" 
            v-model="form.start_date" 
            dateFormat="dd/mm/yy"
            :minDate="new Date()"
            :disabled="submitting"
            showIcon
          />
        </div>

        <div class="flex flex-col gap-2">
          <label for="end_date" class="font-semibold">Data de Término *</label>
          <Calendar 
            id="end_date" 
            v-model="form.end_date" 
            dateFormat="dd/mm/yy"
            :minDate="form.start_date || new Date()"
            :disabled="submitting"
            showIcon
          />
        </div>
      </div>

      <div class="flex flex-col gap-2">
        <label for="reason" class="font-semibold">Motivo da Viagem *</label>
        <Textarea id="reason" v-model="form.reason" rows="3" required :disabled="submitting" />
      </div>

      <div class="flex flex-col gap-2">
        <label for="estimated_cost" class="font-semibold">Custo Estimado (Opcional)</label>
        <InputNumber 
          id="estimated_cost" 
          v-model="form.estimated_cost" 
          mode="currency" 
          currency="BRL" 
          locale="pt-BR"
          :disabled="submitting"
        />
      </div>

      <div class="flex justify-end gap-2 pt-4">
        <Button 
          label="Cancelar" 
          severity="secondary" 
          @click="$emit('update:visible', false)"
          :disabled="submitting"
          type="button"
        />
        <Button 
          :label="isEdit ? 'Salvar' : 'Criar'" 
          type="submit"
          :loading="submitting"
        />
      </div>
    </form>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import type { TravelRequest, CreateTravelRequest } from '@/types'
import { dateToString, stringToDate } from '@/utils/formatters'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Calendar from 'primevue/calendar'
import Textarea from 'primevue/textarea'
import InputNumber from 'primevue/inputnumber'
import Button from 'primevue/button'

interface Props {
  visible: boolean
  request?: TravelRequest | null
  submitting?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  request: null,
  submitting: false
})

const emit = defineEmits<{
  'update:visible': [value: boolean]
  'submit': [data: CreateTravelRequest]
}>()

const isEdit = ref(false)

const form = ref({
  destination: '',
  start_date: null as Date | null,
  end_date: null as Date | null,
  reason: '',
  estimated_cost: undefined as number | undefined
})

const resetForm = () => {
  form.value = {
    destination: '',
    start_date: null,
    end_date: null,
    reason: '',
    estimated_cost: undefined
  }
}

const loadRequest = () => {
  if (props.request) {
    isEdit.value = true
    form.value = {
      destination: props.request.destination,
      start_date: stringToDate(props.request.start_date),
      end_date: stringToDate(props.request.end_date),
      reason: props.request.reason,
      estimated_cost: props.request.estimated_cost
    }
  } else {
    isEdit.value = false
    resetForm()
  }
}

const handleSubmit = () => {
  if (!form.value.start_date || !form.value.end_date) return

  const data: CreateTravelRequest = {
    destination: form.value.destination,
    start_date: dateToString(form.value.start_date),
    end_date: dateToString(form.value.end_date),
    reason: form.value.reason,
    estimated_cost: form.value.estimated_cost
  }

  emit('submit', data)
}

watch(() => props.visible, (newVal) => {
  if (newVal) {
    loadRequest()
  }
})
</script>
