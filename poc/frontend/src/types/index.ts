export interface Cabinet {
  id: number;
  name: string;
  location: string;
  capacity: number;
  currentLoad: number;
  status: 'active' | 'maintenance' | 'offline';
  createdAt: string;
  updatedAt: string;
}

export interface PowerMetric {
  id: number;
  cabinetId: number;
  powerConsumption: number;
  voltage: number;
  current: number;
  temperature: number;
  timestamp: string;
}

export interface PowerMetricRealtime {
  cabinetId: number;
  cabinetName: string;
  powerConsumption: number;
  voltage: number;
  current: number;
  temperature: number;
  timestamp: string;
}

export interface ApiResponse<T> {
  success: boolean;
  data?: T;
  error?: string;
  message?: string;
}