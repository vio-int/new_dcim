import { Router, Request, Response } from 'express';
import { db } from '../services/database';
import { authMiddleware } from '../middleware/auth';
import { ApiResponse, AuthRequest } from '../types';

const router = Router();

// Get all cabinets
router.get('/', async (req: Request, res: Response) => {
  try {
    const cabinets = await db.getCabinets();
    res.json({
      success: true,
      data: cabinets
    } as ApiResponse<typeof cabinets>);
  } catch (error) {
    console.error('Error fetching cabinets:', error);
    res.status(500).json({
      success: false,
      error: 'Failed to fetch cabinets'
    } as ApiResponse<never>);
  }
});

// Get cabinet by ID
router.get('/:id', async (req: Request, res: Response) => {
  try {
    const id = parseInt(req.params.id);
    const cabinet = await db.getCabinetById(id);
    
    if (!cabinet) {
      res.status(404).json({
        success: false,
        error: 'Cabinet not found'
      } as ApiResponse<never>);
      return;
    }
    
    res.json({
      success: true,
      data: cabinet
    } as ApiResponse<typeof cabinet>);
  } catch (error) {
    console.error('Error fetching cabinet:', error);
    res.status(500).json({
      success: false,
      error: 'Failed to fetch cabinet'
    } as ApiResponse<never>);
  }
});

export default router;