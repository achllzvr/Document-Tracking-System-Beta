import React, { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './ui/card';
import { Input } from './ui/input';
import { Label } from './ui/label';
import { Button } from './ui/button';
import { Alert, AlertDescription } from './ui/alert';
import { GraduationCap } from 'lucide-react';
import chedLogo from 'figma:asset/4ec9875a2abae0c471afd06897a613cfc08b40a6.png';

export function Login() {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const { login } = useAuth();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    const success = await login(username, password);
    
    if (!success) {
      setError('Invalid username or password');
    }
    
    setLoading(false);
  };

  return (
    <div className="min-h-screen flex items-center justify-center px-4 py-8 relative" style={{
      background: 'linear-gradient(135deg, #e6eef9 0%, #fef9e6 100%)'
    }}>
      {/* Background logo watermark */}
      <div 
        className="fixed inset-0 pointer-events-none"
        style={{
          backgroundImage: `url(${chedLogo})`,
          backgroundRepeat: 'no-repeat',
          backgroundPosition: 'center center',
          backgroundSize: '500px',
          opacity: 0.02
        }}
      />
      <Card className="w-full max-w-md relative z-10 shadow-xl border-t-4" style={{ borderTopColor: 'var(--ph-blue)' }}>
        <CardHeader className="space-y-4">
          <div className="flex justify-center">
            <div className="p-3 rounded-full" style={{ 
              background: 'linear-gradient(135deg, var(--ph-blue) 0%, var(--ph-blue-light) 100%)',
              boxShadow: '0 4px 12px rgba(0, 56, 168, 0.3)'
            }}>
              <GraduationCap className="w-8 h-8 text-white" />
            </div>
          </div>
          <div className="text-center">
            <CardTitle>CHED HEI Data Portal</CardTitle>
            <CardDescription>
              Sign in to access the Higher Education Information System
            </CardDescription>
          </div>
        </CardHeader>
        <CardContent>
          <form onSubmit={handleSubmit} className="space-y-4">
            {error && (
              <Alert variant="destructive">
                <AlertDescription>{error}</AlertDescription>
              </Alert>
            )}
            
            <div className="space-y-2">
              <Label htmlFor="username">Username</Label>
              <Input
                id="username"
                type="text"
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                placeholder="Enter your username"
                required
              />
            </div>
            
            <div className="space-y-2">
              <Label htmlFor="password">Password</Label>
              <Input
                id="password"
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                placeholder="Enter your password"
                required
              />
            </div>

            <Button 
              type="submit" 
              className="w-full text-white" 
              disabled={loading}
              style={{ 
                background: loading 
                  ? '#999' 
                  : 'linear-gradient(135deg, var(--ph-blue) 0%, var(--ph-blue-light) 100%)',
                boxShadow: loading ? 'none' : '0 2px 8px rgba(0, 56, 168, 0.3)'
              }}
            >
              {loading ? 'Signing in...' : 'Sign In'}
            </Button>

            <div className="mt-6 p-4 bg-gray-50 rounded-lg">
              <p className="text-sm text-gray-600 mb-2">Demo Credentials:</p>
              <div className="space-y-1 text-xs text-gray-500">
                <p><strong>CHED:</strong> ched_admin / admin123</p>
                <p><strong>HEI Head:</strong> hei_head / head123</p>
                <p><strong>HEI Sub-User:</strong> hei_user / user123</p>
              </div>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  );
}
