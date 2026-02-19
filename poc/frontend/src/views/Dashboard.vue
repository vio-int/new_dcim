<script setup lang="ts">
import { ref, computed } from 'vue'
import { use } from 'echarts/core'
import { CanvasRenderer } from 'echarts/renderers'
import { LineChart, BarChart } from 'echarts/charts'
import {
  GridComponent,
  TooltipComponent,
  LegendComponent,
  TitleComponent,
  DataZoomComponent
} from 'echarts/components'
import VChart from 'vue-echarts'
import { useWebSocket } from '../composables/useWebSocket'
import { useCabinets } from '../composables/useCabinets'

use([
  CanvasRenderer,
  LineChart,
  BarChart,
  GridComponent,
  TooltipComponent,
  LegendComponent,
  TitleComponent,
  DataZoomComponent
])

const { connected, metrics } = useWebSocket()
const { cabinets, loading: cabinetsLoading } = useCabinets()

const timeRange = ref('1h')

const totalPower = computed(() => {
  return metrics.value.reduce((sum, m) => sum + m.powerConsumption, 0)
})

const avgTemperature = computed(() => {
  if (metrics.value.length === 0) return 0
  const sum = metrics.value.reduce((acc, m) => acc + m.temperature, 0)
  return (sum / metrics.value.length).toFixed(1)
})

const activeCabinets = computed(() => {
  return cabinets.value.filter(c => c.status === 'active').length
})

// Power consumption chart options
const powerChartOption = computed(() => ({
  title: {
    text: 'Real-time Power Consumption',
    left: 'center',
    textStyle: { fontSize: 16 }
  },
  tooltip: {
    trigger: 'axis',
    formatter: (params: any) => {
      const data = params[0]
      return `${data.name}<br/>${data.seriesName}: ${data.value} W`
    }
  },
  grid: {
    left: '3%',
    right: '4%',
    bottom: '15%',
    containLabel: true
  },
  xAxis: {
    type: 'category',
    data: metrics.value.map(m => m.cabinetName),
    axisLabel: { rotate: 30 }
  },
  yAxis: {
    type: 'value',
    name: 'Power (W)',
    min: 0
  },
  series: [{
    name: 'Power',
    type: 'bar',
    data: metrics.value.map(m => m.powerConsumption),
    itemStyle: {
      color: (params: any) => {
        const value = params.value
        if (value > 3000) return '#ef4444'
        if (value > 2500) return '#f59e0b'
        return '#10b981'
      }
    },
    animationDuration: 500
  }]
}))

// Temperature chart options
const tempChartOption = computed(() => ({
  title: {
    text: 'Cabinet Temperature',
    left: 'center',
    textStyle: { fontSize: 16 }
  },
  tooltip: {
    trigger: 'axis'
  },
  grid: {
    left: '3%',
    right: '4%',
    bottom: '15%',
    containLabel: true
  },
  xAxis: {
    type: 'category',
    data: metrics.value.map(m => m.cabinetName),
    axisLabel: { rotate: 30 }
  },
  yAxis: {
    type: 'value',
    name: 'Temperature (°C)',
    min: 15,
    max: 40
  },
  series: [{
    name: 'Temperature',
    type: 'line',
    data: metrics.value.map(m => m.temperature),
    smooth: true,
    lineStyle: { color: '#3b82f6', width: 3 },
    areaStyle: {
      color: {
        type: 'linear',
        x: 0, y: 0, x2: 0, y2: 1,
        colorStops: [
          { offset: 0, color: 'rgba(59, 130, 246, 0.5)' },
          { offset: 1, color: 'rgba(59, 130, 246, 0.1)' }
        ]
      }
    },
    animationDuration: 500
  }]
}))

// Voltage chart options
const voltageChartOption = computed(() => ({
  title: {
    text: 'Voltage Levels',
    left: 'center',
    textStyle: { fontSize: 16 }
  },
  tooltip: {
    trigger: 'axis'
  },
  grid: {
    left: '3%',
    right: '4%',
    bottom: '15%',
    containLabel: true
  },
  xAxis: {
    type: 'category',
    data: metrics.value.map(m => m.cabinetName),
    axisLabel: { rotate: 30 }
  },
  yAxis: {
    type: 'value',
    name: 'Voltage (V)',
    min: 210,
    max: 230
  },
  series: [{
    name: 'Voltage',
    type: 'line',
    data: metrics.value.map(m => m.voltage),
    lineStyle: { color: '#8b5cf6', width: 2 },
    symbol: 'circle',
    symbolSize: 8,
    animationDuration: 500
  }]
}))
</script>

<template>
  <div class="dashboard">
    <header class="dashboard-header">
      <h1>DCIM Dashboard</h1>
      <div class="connection-status" :class="{ connected }">
        {{ connected ? '🟢 Live' : '🔴 Disconnected' }}
      </div>
    </header>

    <!-- Summary Cards -->
    <div class="summary-cards">
      <div class="card">
        <div class="card-label">Total Power</div>
        <div class="card-value" :class="{ warning: totalPower > 10000 }">
          {{ totalPower.toFixed(0) }} W
        </div>
      </div>
      <div class="card">
        <div class="card-label">Avg Temperature</div>
        <div class="card-value" :class="{ warning: parseFloat(avgTemperature) > 35 }">
          {{ avgTemperature }} °C
        </div>
      </div>
      <div class="card">
        <div class="card-label">Active Cabinets</div>
        <div class="card-value">{{ activeCabinets }} / {{ cabinets.length }}</div>
      </div>
      <div class="card">
        <div class="card-label">Data Points</div>
        <div class="card-value">{{ metrics.length }}</div>
      </div>
    </div>

    <!-- Charts Grid -->
    <div class="charts-grid">
      <div class="chart-container">
        <v-chart class="chart" :option="powerChartOption" autoresize />
      </div>
      <div class="chart-container">
        <v-chart class="chart" :option="tempChartOption" autoresize />
      </div>
      <div class="chart-container">
        <v-chart class="chart" :option="voltageChartOption" autoresize />
      </div>
    </div>

    <!-- Real-time Data Table -->
    <div class="data-table-container">
      <h2>Real-time Sensor Data</h2>
      <table class="data-table">
        <thead>
          <tr>
            <th>Cabinet</th>
            <th>Power (W)</th>
            <th>Voltage (V)</th>
            <th>Current (A)</th>
            <th>Temp (°C)</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="metric in metrics" :key="metric.cabinetId">
            <td>{{ metric.cabinetName }}</td>
            <td :class="{ warning: metric.powerConsumption > 3000 }">
              {{ metric.powerConsumption.toFixed(1) }}
            </td>
            <td>{{ metric.voltage.toFixed(1) }}</td>
            <td>{{ metric.current.toFixed(2) }}</td>
            <td :class="{ warning: metric.temperature > 35 }">
              {{ metric.temperature.toFixed(1) }}
            </td>
            <td>
              <span class="status-badge" 
                    :class="metric.powerConsumption > 3000 ? 'critical' : 'normal'">
                {{ metric.powerConsumption > 3000 ? 'High Load' : 'Normal' }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
.dashboard {
  padding: 20px;
  max-width: 1400px;
  margin: 0 auto;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.dashboard-header h1 {
  margin: 0;
  color: #1f2937;
}

.connection-status {
  padding: 8px 16px;
  border-radius: 20px;
  background: #fee2e2;
  color: #dc2626;
  font-weight: 500;
  font-size: 14px;
}

.connection-status.connected {
  background: #d1fae5;
  color: #059669;
}

.summary-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.card {
  background: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.card-label {
  font-size: 14px;
  color: #6b7280;
  margin-bottom: 8px;
}

.card-value {
  font-size: 28px;
  font-weight: 600;
  color: #1f2937;
}

.card-value.warning {
  color: #dc2626;
}

.charts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 20px;
  margin-bottom: 24px;
}

.chart-container {
  background: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.chart {
  height: 300px;
  width: 100%;
}

.data-table-container {
  background: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.data-table-container h2 {
  margin: 0 0 16px 0;
  color: #1f2937;
  font-size: 18px;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th,
.data-table td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #e5e7eb;
}

.data-table th {
  font-weight: 600;
  color: #6b7280;
  font-size: 12px;
  text-transform: uppercase;
}

.data-table td {
  color: #1f2937;
}

.data-table td.warning {
  color: #dc2626;
  font-weight: 500;
}

.status-badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
}

.status-badge.normal {
  background: #d1fae5;
  color: #059669;
}

.status-badge.critical {
  background: #fee2e2;
  color: #dc2626;
}
</style>