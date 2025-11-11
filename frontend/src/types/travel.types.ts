export interface TravelRequest {
  id: string
  user_id: string
  destination: string
  start_date: string
  end_date: string
  reason: string
  status: 'pending' | 'approved' | 'rejected' | 'cancelled'
  estimated_cost?: number
  rejection_reason?: string
  approved_at?: string
  approved_by?: string
  created_at: string
  updated_at: string
  user?: {
    id: string
    name: string
    email: string
  }
  approver?: {
    id: string
    name: string
    email: string
  }
}

export interface CreateTravelRequest {
  destination: string
  start_date: string
  end_date: string
  reason: string
  estimated_cost?: number
}

export interface UpdateTravelRequest extends Partial<CreateTravelRequest> {
  status?: 'pending' | 'approved' | 'rejected' | 'cancelled'
}

export interface PaginationMeta {
  current_page: number
  per_page: number
  total: number
  last_page: number
}

export interface TravelRequestsResponse {
  success: boolean
  message: string
  data: TravelRequest[]
  pagination?: PaginationMeta
}
