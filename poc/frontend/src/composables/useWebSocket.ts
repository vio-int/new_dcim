import { ref, onMounted, onUnmounted } from 'vue'
import type { PowerMetricRealtime } from '../types'

const WS_URL = import.meta.env.VITE_WS_URL || 'ws://localhost:3001'

export function useWebSocket() {
  const connected = ref(false)
  const metrics = ref<PowerMetricRealtime[]>([])
  const error = ref<string | null>(null)
  
  let ws: WebSocket | null = null
  let reconnectTimeout: number | null = null

  const connect = () => {
    try {
      ws = new WebSocket(WS_URL)

      ws.onopen = () => {
        console.log('WebSocket connected')
        connected.value = true
        error.value = null
      }

      ws.onmessage = (event) => {
        try {
          const message = JSON.parse(event.data)
          
          if (message.type === 'initial' || message.type === 'update') {
            metrics.value = message.data
          }
        } catch (err) {
          console.error('Error parsing WebSocket message:', err)
        }
      }

      ws.onclose = () => {
        console.log('WebSocket disconnected')
        connected.value = false
        
        // Reconnect after 3 seconds
        reconnectTimeout = window.setTimeout(() => {
          console.log('Attempting to reconnect...')
          connect()
        }, 3000)
      }

      ws.onerror = (err) => {
        console.error('WebSocket error:', err)
        error.value = 'Connection error'
      }
    } catch (err) {
      console.error('Failed to create WebSocket:', err)
      error.value = 'Failed to connect'
    }
  }

  const disconnect = () => {
    if (reconnectTimeout) {
      clearTimeout(reconnectTimeout)
      reconnectTimeout = null
    }
    if (ws) {
      ws.close()
      ws = null
    }
  }

  onMounted(() => {
    connect()
  })

  onUnmounted(() => {
    disconnect()
  })

  return {
    connected,
    metrics,
    error,
    connect,
    disconnect
  }
}