export interface Cabinet {
  id: number;
  name: string;
  location: string;
  capacity: number;
  currentLoad: number;
  status: 'active' | 'maintenance' | 'offline';
  createdAt: Date;
  updatedAt: Date;
}

export interface PowerMetric {
  id: number;
  cabinetId: number;
  powerConsumption: number;
  voltage: number;
  current: number;
  temperature: number;
  timestamp: Date;
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

export interface User {
  id: number;
  username: string;
  email: string;
  role: 'admin' | 'operator' | 'viewer';
}

export interface AuthRequest extends Request {
  user?: User;
}

export interface ApiResponse<T> {
  success: boolean;
  data?: T;
  error?: string;
  message?: string;
}