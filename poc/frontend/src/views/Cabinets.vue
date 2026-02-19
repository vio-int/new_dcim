<script setup lang="ts">
import { useCabinets } from '../composables/useCabinets'

const { cabinets, loading, error, refresh } = useCabinets()

const getStatusClass = (status: string) => {
  switch (status) {
    case 'active': return 'status-active'
    case 'maintenance': return 'status-maintenance'
    case 'offline': return 'status-offline'
    default: return ''
  }
}

const getLoadPercentage = (current: number, capacity: number) => {
  return Math.round((current / capacity) * 100)
}
</script>

<template>
  <div class="cabinets-page">
    <header class="page-header">
      <h1>Data Center Cabinets</h1>
      <button class="refresh-btn" @click="refresh" :disabled="loading">
        {{ loading ? 'Loading...' : '🔄 Refresh' }}
      </button>
    </header>

    <div v-if="error" class="error-message">
      ⚠️ {{ error }}
    </div>

    <div v-if="loading" class="loading">
      Loading cabinets...
    </div>

    <div v-else class="cabinets-grid">
      <div v-for="cabinet in cabinets" :key="cabinet.id" class="cabinet-card">
        <div class="cabinet-header">
          <h3>{{ cabinet.name }}</h3>
          <span class="status-badge" :class="getStatusClass(cabinet.status)">
            {{ cabinet.status }}
          </span>
        </div>

        <div class="cabinet-info">
          <div class="info-row">
            <span class="label">Location:</span>
            <span class="value">{{ cabinet.location }}</span>
          </div>
          
          <div class="info-row">
            <span class="label">Capacity:</span>
            <span class="value">{{ cabinet.capacity }} U</span>
          </div>
          
          <div class="info-row">
            <span class="label">Current Load:</span>
            <span class="value">{{ cabinet.currentLoad }} U</span>
          </div>
        </div>

        <div class="load-bar">
          <div class="load-fill" 
               :style="{ width: getLoadPercentage(cabinet.currentLoad, cabinet.capacity) + '%' }"
               :class="{ 
                 high: getLoadPercentage(cabinet.currentLoad, cabinet.capacity) > 80,
                 medium: getLoadPercentage(cabinet.currentLoad, cabinet.capacity) > 50 && getLoadPercentage(cabinet.currentLoad, cabinet.capacity) <= 80
               }">
          </div>
          <span class="load-text">{{ getLoadPercentage(cabinet.currentLoad, cabinet.capacity) }}%</span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.cabinets-page {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.page-header h1 {
  margin: 0;
  color: #1f2937;
}

.refresh-btn {
  padding: 10px 20px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: background 0.2s;
}

.refresh-btn:hover:not(:disabled) {
  background: #2563eb;
}

.refresh-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error-message {
  padding: 16px;
  background: #fee2e2;
  color: #dc2626;
  border-radius: 8px;
  margin-bottom: 20px;
}

.loading {
  text-align: center;
  padding: 40px;
  color: #6b7280;
}

.cabinets-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.cabinet-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s, box-shadow 0.2s;
}

.cabinet-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.cabinet-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.cabinet-header h3 {
  margin: 0;
  color: #1f2937;
  font-size: 18px;
}

.status-badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
  text-transform: capitalize;
}

.status-active {
  background: #d1fae5;
  color: #059669;
}

.status-maintenance {
  background: #fef3c7;
  color: #d97706;
}

.status-offline {
  background: #e5e7eb;
  color: #6b7280;
}

.cabinet-info {
  margin-bottom: 16px;
}

.info-row {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  border-bottom: 1px solid #f3f4f6;
}

.info-row:last-child {
  border-bottom: none;
}

.label {
  color: #6b7280;
  font-size: 14px;
}

.value {
  color: #1f2937;
  font-weight: 500;
  font-size: 14px;
}

.load-bar {
  position: relative;
  height: 24px;
  background: #f3f4f6;
  border-radius: 12px;
  overflow: hidden;
}

.load-fill {
  height: 100%;
  background: #10b981;
  border-radius: 12px;
  transition: width 0.3s ease;
}

.load-fill.medium {
  background: #f59e0b;
}

.load-fill.high {
  background: #ef4444;
}

.load-text {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 12px;
  font-weight: 600;
  color: #374151;
}
</style>