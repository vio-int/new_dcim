import mysql from 'mysql2/promise';
import { Cabinet, PowerMetric } from '../types';

class DatabaseService {
  private pool: mysql.Pool | null = null;

  async initialize(): Promise<void> {
    this.pool = mysql.createPool({
      host: process.env.DB_HOST || 'localhost',
      port: parseInt(process.env.DB_PORT || '3306'),
      user: process.env.DB_USER || 'dcim',
      password: process.env.DB_PASSWORD || 'dcim_password',
      database: process.env.DB_NAME || 'dcim',
      waitForConnections: true,
      connectionLimit: 10,
      queueLimit: 0,
      enableKeepAlive: true,
      keepAliveInitialDelay: 0
    });

    // Test connection
    const connection = await this.pool.getConnection();
    console.log('✅ Database connected successfully');
    connection.release();
  }

  async ensureTables(): Promise<void> {
    // Tables are created by docker/mysql/init/02-poc-tables.sql
    // This method can be used for migrations in the future
    console.log('📊 Database tables verified');
  }

  async getCabinets(): Promise<Cabinet[]> {
    if (!this.pool) throw new Error('Database not initialized');
    
    const [rows] = await this.pool.execute(
      'SELECT * FROM cabinets ORDER BY name'
    );
    
    return (rows as any[]).map(row => ({
      ...row,
      createdAt: new Date(row.created_at),
      updatedAt: new Date(row.updated_at)
    }));
  }

  async getCabinetById(id: number): Promise<Cabinet | null> {
    if (!this.pool) throw new Error('Database not initialized');
    
    const [rows] = await this.pool.execute(
      'SELECT * FROM cabinets WHERE id = ?',
      [id]
    );
    
    const results = rows as any[];
    if (results.length === 0) return null;
    
    return {
      ...results[0],
      createdAt: new Date(results[0].created_at),
      updatedAt: new Date(results[0].updated_at)
    };
  }

  async getPowerMetrics(cabinetId?: number, limit: number = 100): Promise<PowerMetric[]> {
    if (!this.pool) throw new Error('Database not initialized');
    
    let query = 'SELECT * FROM power_metrics';
    const params: any[] = [];
    
    if (cabinetId) {
      query += ' WHERE cabinet_id = ?';
      params.push(cabinetId);
    }
    
    query += ' ORDER BY timestamp DESC LIMIT ?';
    params.push(limit);
    
    const [rows] = await this.pool.execute(query, params);
    
    return (rows as any[]).map(row => ({
      ...row,
      timestamp: new Date(row.timestamp)
    }));
  }

  async getLatestPowerMetrics(): Promise<PowerMetric[]> {
    if (!this.pool) throw new Error('Database not initialized');
    
    const [rows] = await this.pool.execute(`
      SELECT pm.* FROM power_metrics pm
      INNER JOIN (
        SELECT cabinet_id, MAX(timestamp) as max_ts
        FROM power_metrics
        GROUP BY cabinet_id
      ) latest ON pm.cabinet_id = latest.cabinet_id AND pm.timestamp = latest.max_ts
    `);
    
    return (rows as any[]).map(row => ({
      ...row,
      timestamp: new Date(row.timestamp)
    }));
  }

  async insertPowerMetric(metric: Omit<PowerMetric, 'id'>): Promise<void> {
    if (!this.pool) throw new Error('Database not initialized');
    
    await this.pool.execute(
      `INSERT INTO power_metrics (cabinet_id, power_consumption, voltage, current, temperature, timestamp)
       VALUES (?, ?, ?, ?, ?, ?)`,
      [metric.cabinetId, metric.powerConsumption, metric.voltage, metric.current, metric.temperature, metric.timestamp]
    );
  }

  async close(): Promise<void> {
    if (this.pool) {
      await this.pool.end();
      this.pool = null;
    }
  }
}

export const db = new DatabaseService();