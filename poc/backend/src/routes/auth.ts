import { Router, Request, Response } from 'express';
import { AuthService } from '../services/auth';
import { ApiResponse } from '../types';

const router = Router();

// Login
router.post('/login', async (req: Request, res: Response) => {
  try {
    const { username, password } = req.body;

    if (!username || !password) {
      res.status(400).json({
        success: false,
        error: 'Username and password are required'
      } as ApiResponse<never>);
      return;
    }

    const user = AuthService.authenticateUser(username, password);

    if (!user) {
      res.status(401).json({
        success: false,
        error: 'Invalid credentials'
      } as ApiResponse<never>);
      return;
    }

    const token = AuthService.generateToken(user);

    res.json({
      success: true,
      data: {
        user,
        token
      },
      message: 'Login successful'
    } as ApiResponse<any>);
  } catch (error) {
    console.error('Login error:', error);
    res.status(500).json({
      success: false,
      error: 'Login failed'
    } as ApiResponse<never>);
  }
});

// Verify token
router.post('/verify', async (req: Request, res: Response) => {
  try {
    const { token } = req.body;

    if (!token) {
      res.status(400).json({
        success: false,
        error: 'Token is required'
      } as ApiResponse<never>);
      return;
    }

    const user = AuthService.verifyToken(token);

    if (!user) {
      res.status(401).json({
        success: false,
        error: 'Invalid or expired token'
      } as ApiResponse<never>);
      return;
    }

    res.json({
      success: true,
      data: { user },
      message: 'Token is valid'
    } as ApiResponse<any>);
  } catch (error) {
    console.error('Token verification error:', error);
    res.status(500).json({
      success: false,
      error: 'Token verification failed'
    } as ApiResponse<never>);
  }
});

export default router;