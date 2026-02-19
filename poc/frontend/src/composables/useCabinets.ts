import { ref, onMounted } from 'vue'
import { cabinetApi } from '../utils/api'
import type { Cabinet } from '../types'

export function useCabinets() {
  const cabinets = ref<Cabinet[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  const fetchCabinets = async () => {
    loading.value = true
    error.value = null
    
    try {
      const response = await cabinetApi.getAll()
      if (response.data.success && response.data.data) {
        cabinets.value = response.data.data
      } else {
        error.value = response.data.error || 'Failed to fetch cabinets'
      }
    } catch (err: any) {
      error.value = err.message || 'Network error'
    } finally {
      loading.value = false
    }
  }

  onMounted(() => {
    fetchCabinets()
  })

  return {
    cabinets,
    loading,
    error,
    refresh: fetchCabinets
  }
}