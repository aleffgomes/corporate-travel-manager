<template>
  <div class="max-w-7xl mx-auto">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">Todas as Solicitações</h1>
      <p class="text-slate-600 dark:text-slate-400 text-lg">Gerencie todas as solicitações de viagem da empresa</p>
    </div>

    <Card class="shadow-lg">
      <template #content>
        <div class="mb-5 flex flex-wrap gap-3 items-center justify-between">
          <div class="flex gap-3">
            <Dropdown 
              v-model="selectedStatus" 
              :options="statusOptions" 
              optionLabel="label" 
              optionValue="value"
              placeholder="Filtrar por status"
              class="w-48"
              showClear
            />
          </div>

          <Button 
            label="Atualizar" 
            icon="pi pi-refresh" 
            @click="loadRequests" 
            :loading="loading"
            outlined
          />
        </div>

        <DataTable 
          :value="requests" 
          :loading="loading || loadingMore"
          :paginator="false"
          stripedRows
          scrollable
          scrollHeight="600px"
          @scroll="onScroll"
        >
          <Column field="destination" header="Destino" sortable>
            <template #body="{ data }">
              <div class="flex items-center gap-2">
                <i class="pi pi-map-marker text-primary-500"></i>
                <span class="font-medium">{{ data.destination }}</span>
              </div>
            </template>
          </Column>

          <Column field="user.name" header="Solicitante" sortable>
            <template #body="{ data }">
              <div v-if="data.user">
                <p class="font-medium">{{ data.user.name }}</p>
                <p class="text-sm text-slate-500">{{ data.user.email }}</p>
              </div>
            </template>
          </Column>

          <Column field="start_date" header="Período" sortable>
            <template #body="{ data }">
              <div class="text-sm">
                <div>{{ formatDate(data.start_date) }}</div>
                <div class="text-slate-500">até {{ formatDate(data.end_date) }}</div>
              </div>
            </template>
          </Column>

          <Column field="estimated_cost" header="Custo" sortable>
            <template #body="{ data }">
              <span v-if="data.estimated_cost" class="font-semibold">
                {{ formatCurrency(data.estimated_cost) }}
              </span>
              <span v-else class="text-slate-400">-</span>
            </template>
          </Column>

          <Column field="status" header="Status" sortable>
            <template #body="{ data }">
              <Tag :value="getStatusLabel(data.status)" :severity="getStatusSeverity(data.status)" />
            </template>
          </Column>

          <Column header="Ações" :exportable="false" style="min-width: 150px">
            <template #body="{ data }">
              <div class="flex gap-2">
                <Button 
                  icon="pi pi-eye" 
                  size="small"
                  text 
                  rounded
                  @click="viewRequest(data)"
                  v-tooltip.top="'Ver detalhes'"
                />
                
                <Button 
                  v-if="data.status === 'pending'"
                  icon="pi pi-check" 
                  severity="success"
                  size="small"
                  text 
                  rounded
                  @click="openStatusDialog(data, 'approve')"
                  v-tooltip.top="'Aprovar'"
                />
                
                <Button 
                  v-if="data.status === 'pending'"
                  icon="pi pi-times" 
                  severity="danger"
                  size="small"
                  text 
                  rounded
                  @click="openStatusDialog(data, 'reject')"
                  v-tooltip.top="'Rejeitar'"
                />

                <Button 
                  icon="pi pi-trash" 
                  severity="danger"
                  size="small"
                  text 
                  rounded
                  @click="confirmDelete(data)"
                  v-tooltip.top="'Excluir'"
                />
              </div>
            </template>
          </Column>
        </DataTable>
      </template>
    </Card>

    <!-- Dialogs -->
    <TravelRequestDetailsDialog 
      v-model:visible="showDetailsDialog"
      :request="selectedRequest"
    />

    <TravelRequestStatusDialog
      v-model:visible="showStatusDialog"
      :request="selectedRequest"
      :action="statusAction"
      :submitting="submitting"
      @confirm="handleStatusUpdate"
    />

    <TravelRequestDeleteDialog
      v-model:visible="showDeleteDialog"
      :request="selectedRequest"
      :submitting="submitting"
      @confirm="handleDelete"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useToast } from 'primevue/usetoast'
import { travelService } from '@/services/travel.service'
import { formatDate, formatCurrency } from '@/utils/formatters'
import type { TravelRequest } from '@/types'
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Dropdown from 'primevue/dropdown'
import TravelRequestDetailsDialog from '@/components/travel/TravelRequestDetailsDialog.vue'
import TravelRequestStatusDialog from '@/components/travel/TravelRequestStatusDialog.vue'
import TravelRequestDeleteDialog from '@/components/travel/TravelRequestDeleteDialog.vue'

const toast = useToast()
const loading = ref(false)
const loadingMore = ref(false)
const submitting = ref(false)
const requests = ref<TravelRequest[]>([])
const selectedStatus = ref<string | null>(null)
const showDetailsDialog = ref(false)
const showStatusDialog = ref(false)
const showDeleteDialog = ref(false)
const selectedRequest = ref<TravelRequest | null>(null)
const statusAction = ref<'approve' | 'reject'>('approve')

// Paginação
const currentPage = ref(1)
const lastPage = ref(1)

const statusOptions = [
  { label: 'Todos', value: null },
  { label: 'Pendente', value: 'pending' },
  { label: 'Aprovado', value: 'approved' },
  { label: 'Rejeitado', value: 'rejected' },
  { label: 'Cancelado', value: 'cancelled' }
]

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

const loadRequests = async (append = false) => {
  if (!append) {
    loading.value = true
    currentPage.value = 1
    requests.value = []
  } else {
    loadingMore.value = true
  }
  
  try {
    const response = await travelService.getAll({
      page: currentPage.value,
      per_page: 15,
      my_requests: false,
      status: selectedStatus.value || undefined
    })
    
    if (append) {
      const existingIds = new Set(requests.value.map(r => r.id))
      const newItems = response.data.filter(item => !existingIds.has(item.id))
      requests.value = [...requests.value, ...newItems]
    } else {
      requests.value = response.data
    }
    
    if (response.pagination) {
      lastPage.value = response.pagination.last_page
    }
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: 'Não foi possível carregar as solicitações',
      life: 3000
    })
  } finally {
    loading.value = false
    loadingMore.value = false
  }
}

const loadMore = async () => {
  if (loadingMore.value || loading.value || currentPage.value >= lastPage.value) return
  
  currentPage.value++
  await loadRequests(true)
}

const onScroll = (event: any) => {
  const { scrollTop, scrollHeight, clientHeight } = event.target
  const threshold = 100
  
  if (scrollHeight - scrollTop - clientHeight < threshold) {
    loadMore()
  }
}

const viewRequest = (request: TravelRequest) => {
  selectedRequest.value = request
  showDetailsDialog.value = true
}

const openStatusDialog = (request: TravelRequest, action: 'approve' | 'reject') => {
  selectedRequest.value = request
  statusAction.value = action
  showStatusDialog.value = true
}

const handleStatusUpdate = async (rejectionReason?: string) => {
  if (!selectedRequest.value) return

  submitting.value = true
  try {
    const status = statusAction.value === 'approve' ? 'approved' : 'rejected'
    await travelService.updateStatus(selectedRequest.value.id, status, rejectionReason)
    
    toast.add({
      severity: 'success',
      summary: 'Sucesso',
      detail: `Solicitação ${status === 'approved' ? 'aprovada' : 'rejeitada'} com sucesso`,
      life: 3000
    })
    
    showStatusDialog.value = false
    await loadRequests()
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: 'Não foi possível atualizar o status',
      life: 3000
    })
  } finally {
    submitting.value = false
  }
}

const confirmDelete = (request: TravelRequest) => {
  selectedRequest.value = request
  showDeleteDialog.value = true
}

const handleDelete = async () => {
  if (!selectedRequest.value) return

  submitting.value = true
  try {
    await travelService.delete(selectedRequest.value.id)
    toast.add({
      severity: 'success',
      summary: 'Sucesso',
      detail: 'Solicitação excluída com sucesso',
      life: 3000
    })
    showDeleteDialog.value = false
    await loadRequests()
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: 'Não foi possível excluir a solicitação',
      life: 3000
    })
  } finally {
    submitting.value = false
  }
}

watch(selectedStatus, () => {
  loadRequests(false)
})

onMounted(() => {
  loadRequests()
})
</script>
