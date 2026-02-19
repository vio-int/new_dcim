import jwt from 'jsonwebtoken';
import { User } from '../types';

const JWT_SECRET = process.env.JWT_SECRET || 'dcim-poc-secret-key';
const JWT_EXPIRES_IN = process.env.JWT_EXPIRES_IN || '24h';

// Mock users for PoC
const MOCK_USERS: User[] = [
  { id: 1, username: 'admin', email: 'admin@dcim.local', role: 'admin' },
  { id: 2, username: 'operator', email: 'operator@dcim.local', role: 'operator' },
  { id: 3, username: 'viewer', email: 'viewer@dcim.local', role: 'viewer' }
];

export class AuthService {
  static generateToken(user: User): string {
    return jwt.sign(
      { id: user.id, username: user.username, role: user.role },
      JWT_SECRET,
      { expiresIn: JWT_EXPIRES_IN as any }
    );
  }

  static verifyToken(token: string): User | null {
    try {
      const decoded = jwt.verify(token, JWT_SECRET) as any;
      return {
        id: decoded.id,
        username: decoded.username,
        email: decoded.email || '',
        role: decoded.role
      };
    } catch (error) {
      return null;
    }
  }

  static authenticateUser(username: string, password: string): User | null {
    // Simple mock authentication for PoC
    // In production, this would check hashed passwords
    const user = MOCK_USERS.find(u => u.username === username);
    if (user && password === 'password') {
      return user;
    }
    return null;
  }

  static getUserById(id: number): User | null {
    return MOCK_USERS.find(u => u.id === id) || null;
  }
}