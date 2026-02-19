import { Router, Request, Response } from 'express';
import { db } from '../services/database';
import { ApiResponse } from '../types';

const router = Router();

// Get power metrics
router.get('/power', async (req: Request, res: Response) => {
  try {
    const cabinetId = req.query.cabinetId 
      ? parseInt(req.query.cabinetId as string) 
      : undefined;
    const limit = req.query.limit 
      ? parseInt(req.query.limit as string) 
      : 100;

    const metrics = await db.getPowerMetrics(cabinetId, limit);
    
    res.json({
      success: true,
      data: metrics
    } as ApiResponse<typeof metrics>);
  } catch (error) {
    console.error('Error fetching power metrics:', error);
    res.status(500).json({
      success: false,
      error: 'Failed to fetch power metrics'
    } as ApiResponse<never>);
  }
});

// Get latest power metrics for all cabinets
router.get('/power/latest', async (req: Request, res: Response) => {
  try {
    const metrics = await db.getLatestPowerMetrics();
    
    res.json({
      success: true,
      data: metrics
    } as ApiResponse<typeof metrics>);
  } catch (error) {
    console.error('Error fetching latest power metrics:', error);
    res.status(500).json({
      success: false,
      error: 'Failed to fetch latest power metrics'
    } as ApiResponse<never>);
  }
});

export default router;