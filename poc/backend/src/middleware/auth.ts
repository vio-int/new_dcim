import { Request, Response, NextFunction } from 'express';
import { AuthService } from '../services/auth';
import { AuthRequest, ApiResponse } from '../types';

export const authMiddleware = (req: AuthRequest, res: Response, next: NextFunction): void => {
  const authHeader = (req.headers as any)['authorization'];
  
  if (!authHeader || typeof authHeader !== 'string' || !authHeader.startsWith('Bearer ')) {
    res.status(401).json({
      success: false,
      error: 'Authorization token required'
    } as ApiResponse<never>);
    return;
  }

  const token = authHeader.substring(7);
  const user = AuthService.verifyToken(token);

  if (!user) {
    res.status(401).json({
      success: false,
      error: 'Invalid or expired token'
    } as ApiResponse<never>);
    return;
  }

  req.user = user;
  next();
};

export const optionalAuthMiddleware = (req: AuthRequest, res: Response, next: NextFunction): void => {
  const authHeader = (req.headers as any)['authorization'];
  
  if (authHeader && typeof authHeader === 'string' && authHeader.startsWith('Bearer ')) {
    const token = authHeader.substring(7);
    const user = AuthService.verifyToken(token);
    if (user) {
      req.user = user;
    }
  }
  
  next();
};