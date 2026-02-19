import WebSocket from 'ws';
import { db } from './database';
import { PowerMetricRealtime } from '../types';

export class WebSocketService {
  private wss: WebSocket.Server | null = null;
  private clients: Set<WebSocket> = new Set();
  private simulationInterval: NodeJS.Timeout | null = null;

  start(port: number): void {
    this.wss = new WebSocket.Server({ port });

    this.wss.on('connection', (ws: WebSocket) => {
      console.log('🔌 WebSocket client connected');
      this.clients.add(ws);

      // Send initial data
      this.sendLatestMetrics(ws);

      ws.on('close', () => {
        console.log('🔌 WebSocket client disconnected');
        this.clients.delete(ws);
      });

      ws.on('error', (error) => {
        console.error('WebSocket error:', error);
        this.clients.delete(ws);
      });
    });

    console.log(`🔌 WebSocket server started on port ${port}`);
    
    // Start simulated data streaming
    this.startSimulation();
  }

  private async sendLatestMetrics(ws: WebSocket): Promise<void> {
    try {
      const metrics = await db.getLatestPowerMetrics();
      const cabinets = await db.getCabinets();
      
      const realtimeData: PowerMetricRealtime[] = metrics.map(metric => {
        const cabinet = cabinets.find(c => c.id === metric.cabinetId);
        return {
          cabinetId: metric.cabinetId,
          cabinetName: cabinet?.name || `Cabinet ${metric.cabinetId}`,
          powerConsumption: metric.powerConsumption,
          voltage: metric.voltage,
          current: metric.current,
          temperature: metric.temperature,
          timestamp: metric.timestamp.toISOString()
        };
      });

      if (ws.readyState === WebSocket.OPEN) {
        ws.send(JSON.stringify({
          type: 'initial',
          data: realtimeData
        }));
      }
    } catch (error) {
      console.error('Error sending initial metrics:', error);
    }
  }

  private startSimulation(): void {
    // Send simulated real-time updates every 2 seconds
    this.simulationInterval = setInterval(async () => {
      try {
        const cabinets = await db.getCabinets();
        
        const realtimeData: PowerMetricRealtime[] = cabinets.map(cabinet => {
          // Generate realistic-looking sensor data with some randomness
          const basePower = 2000 + (cabinet.id * 500); // Base load per cabinet
          const variation = (Math.random() - 0.5) * 200; // ±100W variation
          const powerConsumption = Math.round(basePower + variation);
          
          const voltage = 220 + (Math.random() - 0.5) * 4; // 218-222V
          const current = powerConsumption / voltage;
          const temperature = 22 + (powerConsumption / 1000) * 5 + (Math.random() - 0.5) * 2;

          return {
            cabinetId: cabinet.id,
            cabinetName: cabinet.name,
            powerConsumption: Math.round(powerConsumption * 10) / 10,
            voltage: Math.round(voltage * 10) / 10,
            current: Math.round(current * 100) / 100,
            temperature: Math.round(temperature * 10) / 10,
            timestamp: new Date().toISOString()
          };
        });

        this.broadcast({
          type: 'update',
          data: realtimeData
        });
      } catch (error) {
        console.error('Error in simulation:', error);
      }
    }, 2000);
  }

  private broadcast(message: any): void {
    const messageStr = JSON.stringify(message);
    this.clients.forEach(ws => {
      if (ws.readyState === WebSocket.OPEN) {
        ws.send(messageStr);
      }
    });
  }

  stop(): void {
    if (this.simulationInterval) {
      clearInterval(this.simulationInterval);
      this.simulationInterval = null;
    }

    this.clients.forEach(ws => {
      ws.close();
    });
    this.clients.clear();

    if (this.wss) {
      this.wss.close();
      this.wss = null;
    }
  }
}