<template>
  <div class="max-w-7xl mx-auto">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">Minhas Solicitações</h1>
      <p class="text-slate-600 dark:text-slate-400 text-lg">Gerencie suas solicitações de viagem</p>
    </div>

    <Card class="shadow-lg">
      <template #content>
        <div class="mb-5 flex flex-wrap gap-3 items-center justify-between">
          <Button 
            label="Nova Solicitação" 
            icon="pi pi-plus"
            size="large"
            @click="createNewRequest"
          />
          
          <div class="flex gap-3 items-center">
            <Dropdown 
              v-model="selectedStatus" 
              :options="statusOptions" 
              optionLabel="label" 
              optionValue="value"
              placeholder="Filtrar por status"
              class="w-48"
              showClear
            />
            <Button 
              icon="pi pi-refresh" 
              @click="loadRequests" 
              :loading="loading"
              outlined
            />
          </div>
        </div>

        <DataTable 
          :value="requests" 
          :loading="loading"
          :paginator="true"
          :rows="15"
          :totalRecords="totalRecords"
          :lazy="true"
          @page="onPage"
          :rowsPerPageOptions="[10, 15, 25, 50]"
          stripedRows
          paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
          currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} solicitações"
        >
          <Column field="destination" header="Destino" sortable>
            <template #body="{ data }">
              <div class="flex items-center gap-2">
                <i class="pi pi-map-marker text-primary-500"></i>
                <span class="font-medium">{{ data.destination }}</span>
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

          <Column field="reason" header="Motivo">
            <template #body="{ data }">
              <span class="text-sm">{{ truncate(data.reason, 50) }}</span>
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
                  icon="pi pi-pencil" 
                  severity="info"
                  size="small"
                  text 
                  rounded
                  @click="editRequest(data)"
                  v-tooltip.top="'Editar'"
                />
                
                <Button 
                  v-if="data.status === 'pending'"
                  icon="pi pi-times-circle" 
                  severity="warning"
                  size="small"
                  text 
                  rounded
                  @click="confirmCancel(data)"
                  v-tooltip.top="'Cancelar'"
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

          <template #empty>
            <div class="text-center py-8">
              <i class="pi pi-inbox text-5xl text-slate-300 mb-4"></i>
              <p class="text-slate-500">Nenhuma solicitação encontrada</p>
            </div>
          </template>
        </DataTable>
      </template>
    </Card>

    <!-- Dialogs -->
    <TravelRequestFormDialog 
      v-model:visible="showFormDialog"
      :request="selectedRequest"
      :submitting="submitting"
      @submit="handleCreateOrUpdate"
    />

    <TravelRequestDetailsDialog 
      v-model:visible="showDetailsDialog"
      :request="selectedRequest"
    />

    <TravelRequestDeleteDialog
      v-model:visible="showDeleteDialog"
      :request="selectedRequest"
      :submitting="submitting"
      @confirm="handleDelete"
    />

    <TravelRequestCancelDialog
      v-model:visible="showCancelDialog"
      :request="selectedRequest"
      :submitting="submitting"
      @confirm="handleCancel"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useToast } from 'primevue/usetoast'
import { travelService } from '@/services/travel.service'
import { formatDate, formatCurrency, truncate } from '@/utils/formatters'
import type { TravelRequest, CreateTravelRequest } from '@/types'
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Dropdown from 'primevue/dropdown'
import TravelRequestFormDialog from '@/components/travel/TravelRequestFormDialog.vue'
import TravelRequestDetailsDialog from '@/components/travel/TravelRequestDetailsDialog.vue'
import TravelRequestDeleteDialog from '@/components/travel/TravelRequestDeleteDialog.vue'
import TravelRequestCancelDialog from '@/components/travel/TravelRequestCancelDialog.vue'

const toast = useToast()
const loading = ref(false)
const submitting = ref(false)
const requests = ref<TravelRequest[]>([])
const selectedStatus = ref<string | null>(null)
const showFormDialog = ref(false)
const showDetailsDialog = ref(false)
const showDeleteDialog = ref(false)
const showCancelDialog = ref(false)
const selectedRequest = ref<TravelRequest | null>(null)

// Paginação
const currentPage = ref(1)
const rowsPerPage = ref(15)
const totalRecords = ref(0)

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

const loadRequests = async () => {
  loading.value = true
  
  try {
    const response = await travelService.getAll({
      page: currentPage.value,
      per_page: rowsPerPage.value,
      my_requests: true,
      status: selectedStatus.value || undefined
    })
    
    requests.value = response.data
    
    if (response.pagination) {
      totalRecords.value = response.pagination.total
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
  }
}

const onPage = (event: any) => {
  currentPage.value = event.page + 1
  rowsPerPage.value = event.rows
  loadRequests()
}

const handleCreateOrUpdate = async (data: CreateTravelRequest) => {
  submitting.value = true
  try {
    if (selectedRequest.value) {
      // Editar
      await travelService.update(selectedRequest.value.id, data)
      toast.add({
        severity: 'success',
        summary: 'Sucesso',
        detail: 'Solicitação atualizada com sucesso',
        life: 3000
      })
    } else {
      // Criar
      await travelService.create(data)
      toast.add({
        severity: 'success',
        summary: 'Sucesso',
        detail: 'Solicitação criada com sucesso',
        life: 3000
      })
    }
    showFormDialog.value = false
    selectedRequest.value = null
    await loadRequests()
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: 'Não foi possível salvar a solicitação',
      life: 3000
    })
  } finally {
    submitting.value = false
  }
}

const createNewRequest = () => {
  selectedRequest.value = null
  showFormDialog.value = true
}

const viewRequest = (request: TravelRequest) => {
  selectedRequest.value = request
  showDetailsDialog.value = true
}

const editRequest = (request: TravelRequest) => {
  selectedRequest.value = request
  showFormDialog.value = true
}

const confirmCancel = (request: TravelRequest) => {
  selectedRequest.value = request
  showCancelDialog.value = true
}

const handleCancel = async () => {
  if (!selectedRequest.value) return

  submitting.value = true
  try {
    await travelService.cancel(selectedRequest.value.id)
    toast.add({
      severity: 'success',
      summary: 'Sucesso',
      detail: 'Solicitação cancelada com sucesso',
      life: 3000
    })
    showCancelDialog.value = false
    selectedRequest.value = null
    await loadRequests()
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: 'Não foi possível cancelar a solicitação',
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
    selectedRequest.value = null
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
  currentPage.value = 1
  loadRequests()
})

onMounted(() => {
  loadRequests()
})
</script>
