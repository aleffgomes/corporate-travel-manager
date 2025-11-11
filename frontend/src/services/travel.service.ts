import api from './api'
import type { TravelRequest, CreateTravelRequest, UpdateTravelRequest, TravelRequestsResponse } from '@/types'

export const travelService = {
  async getAll(params?: { 
    page?: number
    per_page?: number
    my_requests?: boolean
    status?: string
    destination?: string
  }): Promise<TravelRequestsResponse> {
    const response = await api.get<TravelRequestsResponse>('/v1/travel-requests', { params })
    return response.data
  },

  async getById(id: string): Promise<TravelRequest> {
    const response = await api.get<{ data: TravelRequest }>(`/v1/travel-requests/${id}`)
    return response.data.data
  },

  async create(data: CreateTravelRequest): Promise<TravelRequest> {
    const response = await api.post<{ data: TravelRequest }>('/v1/travel-requests', data)
    return response.data.data
  },

  async update(id: string, data: UpdateTravelRequest): Promise<TravelRequest> {
    const response = await api.put<{ data: TravelRequest }>(`/v1/travel-requests/${id}`, data)
    return response.data.data
  },

  async delete(id: string): Promise<void> {
    await api.delete(`/v1/travel-requests/${id}`)
  },

  async updateStatus(id: string, status: TravelRequest['status'], rejectionReason?: string): Promise<TravelRequest> {
    const response = await api.patch<{ data: TravelRequest }>(`/v1/travel-requests/${id}/status`, { 
      status,
      rejection_reason: rejectionReason
    })
    return response.data.data
  },

  async cancel(id: string): Promise<TravelRequest> {
    const response = await api.post<{ data: TravelRequest }>(`/v1/travel-requests/${id}/cancel`)
    return response.data.data
  }
}
