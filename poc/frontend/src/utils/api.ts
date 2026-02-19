import axios from 'axios'
import type { ApiResponse, Cabinet, PowerMetric } from '../types'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:3000'

const api = axios.create({
  baseURL: `${API_URL}/api`,
  headers: {
    'Content-Type': 'application/json'
  }
})

// Add auth token to requests
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

export const cabinetApi = {
  getAll: () => api.get<ApiResponse<Cabinet[]>>('/cabinets'),
  getById: (id: number) => api.get<ApiResponse<Cabinet>>(`/cabinets/${id}`)
}

export const metricsApi = {
  getPower: (cabinetId?: number, limit?: number) => 
    api.get<ApiResponse<PowerMetric[]>>('/metrics/power', { 
      params: { cabinetId, limit } 
    }),
  getLatest: () => api.get<ApiResponse<PowerMetric[]>>('/metrics/power/latest')
}

export const authApi = {
  login: (username: string, password: string) => 
    api.post<ApiResponse<{ user: any; token: string }>>('/auth/login', { username, password }),
  verify: (token: string) => 
    api.post<ApiResponse<{ user: any }>>('/auth/verify', { token })
}

export default api