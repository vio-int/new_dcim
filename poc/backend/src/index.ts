import express from 'express';
import cors from 'cors';
import dotenv from 'dotenv';
import { db } from './services/database';
import { WebSocketService } from './services/websocket';
import { errorHandler, notFoundHandler } from './middleware/error';
import authRoutes from './routes/auth';
import cabinetRoutes from './routes/cabinets';
import metricsRoutes from './routes/metrics';

dotenv.config();

const app = express();
const PORT = parseInt(process.env.PORT || '3000');
const WS_PORT = parseInt(process.env.WS_PORT || '3001');

// Middleware
app.use(cors({
  origin: process.env.NODE_ENV === 'production' 
    ? ['http://localhost:5173', 'http://localhost:4173'] 
    : '*',
  credentials: true
}));
app.use(express.json());

// Request logging
app.use((req, res, next) => {
  console.log(`${new Date().toISOString()} - ${req.method} ${req.path}`);
  next();
});

// Health check
app.get('/health', (req, res) => {
  res.json({ 
    status: 'ok', 
    timestamp: new Date().toISOString(),
    service: 'dcim-poc-backend'
  });
});

// API Routes
app.use('/api/auth', authRoutes);
app.use('/api/cabinets', cabinetRoutes);
app.use('/api/metrics', metricsRoutes);

// Error handling
app.use(notFoundHandler);
app.use(errorHandler);

// Initialize services
async function startServer() {
  try {
    // Connect to database
    await db.initialize();
    await db.ensureTables();

    // Start WebSocket server
    const wsService = new WebSocketService();
    wsService.start(WS_PORT);

    // Start HTTP server
    app.listen(PORT, '0.0.0.0', () => {
      console.log(`🚀 API server running on port ${PORT}`);
      console.log(`📊 Health check: http://localhost:${PORT}/health`);
      console.log(`🔌 WebSocket server running on port ${WS_PORT}`);
    });

    // Graceful shutdown
    process.on('SIGTERM', async () => {
      console.log('SIGTERM received, shutting down gracefully');
      wsService.stop();
      await db.close();
      process.exit(0);
    });

    process.on('SIGINT', async () => {
      console.log('SIGINT received, shutting down gracefully');
      wsService.stop();
      await db.close();
      process.exit(0);
    });

  } catch (error) {
    console.error('Failed to start server:', error);
    process.exit(1);
  }
}

startServer();