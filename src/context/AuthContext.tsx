import React, { createContext, useContext, useState, useEffect } from 'react';
import { User, UserRole } from '../types';

interface AuthContextType {
  user: User | null;
  login: (username: string, password: string) => Promise<boolean>;
  logout: () => void;
  isAuthenticated: boolean;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

// Mock users for demo
const mockUsers: Record<string, { password: string; user: User }> = {
  'ched_admin': {
    password: 'admin123',
    user: {
      id: 'ched-1',
      username: 'ched_admin',
      role: 'CHED',
      firstName: 'Maria',
      lastName: 'Santos'
    }
  },
  'hei_head': {
    password: 'head123',
    user: {
      id: 'hei-h-1',
      username: 'hei_head',
      role: 'HEI_HEAD',
      heiId: 'hei-1',
      heiName: 'University of the Philippines',
      firstName: 'Juan',
      lastName: 'Dela Cruz'
    }
  },
  'hei_user': {
    password: 'user123',
    user: {
      id: 'hei-u-1',
      username: 'hei_user',
      role: 'HEI_SUB_USER',
      heiId: 'hei-1',
      heiName: 'University of the Philippines',
      firstName: 'Ana',
      lastName: 'Reyes'
    }
  }
};

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null);

  useEffect(() => {
    // Check for stored session
    const storedUser = localStorage.getItem('ched_user');
    if (storedUser) {
      setUser(JSON.parse(storedUser));
    }
  }, []);

  const login = async (username: string, password: string): Promise<boolean> => {
    const userRecord = mockUsers[username];
    if (userRecord && userRecord.password === password) {
      setUser(userRecord.user);
      localStorage.setItem('ched_user', JSON.stringify(userRecord.user));
      return true;
    }
    return false;
  };

  const logout = () => {
    setUser(null);
    localStorage.removeItem('ched_user');
  };

  return (
    <AuthContext.Provider value={{ user, login, logout, isAuthenticated: !!user }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}
