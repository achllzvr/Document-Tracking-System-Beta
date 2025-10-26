import React, { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { 
  LayoutDashboard, Ticket, FileText, Users, GraduationCap, 
  Building2, UserPlus, Calendar, Bell, LogOut, Menu, X
} from 'lucide-react';
import { Button } from './ui/button';
import { Badge } from './ui/badge';
import { 
  DropdownMenu, 
  DropdownMenuContent, 
  DropdownMenuItem, 
  DropdownMenuTrigger,
  DropdownMenuSeparator,
  DropdownMenuLabel
} from './ui/dropdown-menu';
import { ScrollArea } from './ui/scroll-area';
import chedLogo from 'figma:asset/4ec9875a2abae0c471afd06897a613cfc08b40a6.png';

interface HEILayoutProps {
  children: React.ReactNode;
  currentPage: string;
  onNavigate: (page: string) => void;
}

export function HEILayout({ children, currentPage, onNavigate }: HEILayoutProps) {
  const { user, logout } = useAuth();
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [notifications] = useState(3);

  // Philippine flag colors rotation with transparency
  const phColors = [
    { bg: 'rgba(206, 17, 38, 0.85)', border: 'var(--ph-yellow)', name: 'red' },
    { bg: 'rgba(252, 209, 22, 0.85)', border: 'var(--ph-blue)', name: 'yellow', textColor: '#333' },
    { bg: 'rgba(0, 56, 168, 0.85)', border: 'var(--ph-red)', name: 'blue' }
  ];

  const getColorForIndex = (index: number) => {
    return phColors[index % phColors.length];
  };

  const menuItems = [
    { id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard },
    { id: 'view-tickets', label: 'Tickets', icon: Ticket },
    { id: 'enrollment', label: 'Enrollment Data', icon: FileText },
    { id: 'faculty', label: 'Faculty Data', icon: Users },
    { id: 'graduates', label: 'Graduates Data', icon: GraduationCap },
    { id: 'institution-profile', label: 'Institution Profile', icon: Building2, headOnly: true },
    { id: 'users', label: 'Sub-Users', icon: UserPlus, headOnly: true },
    { id: 'set-calendar', label: 'Calendar', icon: Calendar },
  ];

  const visibleMenuItems = menuItems.filter(item => 
    !item.headOnly || user?.role === 'HEI_HEAD'
  );

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-white border-b-2 sticky top-0 z-50 shadow-sm" style={{ borderBottomColor: 'var(--ph-blue)' }}>
        <div className="flex items-center justify-between px-4 sm:px-6 lg:px-8 py-3">
          <div className="flex items-center gap-4">
            <button
              onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
              className="lg:hidden p-2 hover:bg-blue-50 rounded-lg transition-colors"
            >
              {mobileMenuOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
            </button>
            
            <div className="flex items-center gap-3">
              <div className="p-2 rounded-lg" style={{ background: 'linear-gradient(135deg, var(--ph-blue) 0%, var(--ph-blue-light) 100%)' }}>
                <Building2 className="w-6 h-6 text-white" />
              </div>
              <div>
                <h1 className="text-lg" style={{ color: 'var(--ph-blue)' }}>{user?.heiName}</h1>
                <p className="text-xs text-gray-500">HEI Portal</p>
              </div>
            </div>
          </div>

          <div className="flex items-center gap-3">
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button variant="ghost" className="gap-2 relative hover:bg-blue-50">
                  <Bell className="w-5 h-5" style={{ color: 'var(--ph-blue)' }} />
                  {notifications > 0 && (
                    <span className="absolute top-1 right-1 w-5 h-5 rounded-full flex items-center justify-center text-xs text-white" style={{ backgroundColor: 'var(--ph-red)' }}>
                      {notifications}
                    </span>
                  )}
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end" className="w-80">
                <DropdownMenuLabel>Notifications</DropdownMenuLabel>
                <DropdownMenuSeparator />
                <div className="space-y-2 p-2">
                  <div className="p-2 hover:bg-gray-50 rounded-md cursor-pointer">
                    <p className="text-sm">New ticket assigned: Q1 2025 Enrollment</p>
                    <p className="text-xs text-gray-500">2 hours ago</p>
                  </div>
                  <div className="p-2 hover:bg-gray-50 rounded-md cursor-pointer">
                    <p className="text-sm">Ticket due in 3 days</p>
                    <p className="text-xs text-gray-500">1 day ago</p>
                  </div>
                  <div className="p-2 hover:bg-gray-50 rounded-md cursor-pointer">
                    <p className="text-sm">ETL upload successful</p>
                    <p className="text-xs text-gray-500">2 days ago</p>
                  </div>
                </div>
                <DropdownMenuSeparator />
                <DropdownMenuItem className="justify-center">
                  Mark all as read
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>

            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button variant="ghost" className="gap-2 hover:bg-blue-50">
                  <div className="w-8 h-8 rounded-full flex items-center justify-center text-white" style={{ background: 'linear-gradient(135deg, var(--ph-blue) 0%, var(--ph-blue-light) 100%)' }}>
                    {user?.firstName[0]}{user?.lastName[0]}
                  </div>
                  <div className="text-left hidden md:block">
                    <p className="text-sm">{user?.firstName} {user?.lastName}</p>
                    <p className="text-xs text-gray-500">
                      {user?.role === 'HEI_HEAD' ? 'Head User' : 'Sub-User'}
                    </p>
                  </div>
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end">
                <DropdownMenuItem onClick={logout}>
                  <LogOut className="w-4 h-4 mr-2" />
                  Logout
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
        </div>
      </header>

      <div className="flex">
        {/* Sidebar */}
        <aside className={`
          fixed lg:sticky top-[57px] left-0 h-[calc(100vh-57px)] bg-white border-r border-gray-200 
          transition-transform duration-300 z-40
          ${mobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'}
        `}>
          <ScrollArea className="h-full w-64">
            <nav className="p-3 lg:p-4 space-y-1">
              {visibleMenuItems.map((item, index) => {
                const Icon = item.icon;
                const isActive = currentPage === item.id;
                const color = getColorForIndex(index);
                
                return (
                  <button
                    key={item.id}
                    onClick={() => {
                      onNavigate(item.id);
                      setMobileMenuOpen(false);
                    }}
                    className={`
                      w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all
                      ${isActive 
                        ? 'shadow-md' 
                        : 'text-gray-700 hover:bg-blue-50'
                      }
                    `}
                    style={isActive ? { 
                      backgroundColor: color.bg,
                      borderLeft: `3px solid ${color.border}`,
                      color: color.textColor || '#ffffff'
                    } : {}}
                  >
                    <Icon className="w-5 h-5" />
                    <span>{item.label}</span>
                  </button>
                );
              })}
            </nav>
          </ScrollArea>
        </aside>

        {/* Mobile overlay */}
        {mobileMenuOpen && (
          <div 
            className="fixed inset-0 bg-black/50 z-30 lg:hidden"
            onClick={() => setMobileMenuOpen(false)}
          />
        )}

        {/* Main content */}
        <main className="flex-1 relative min-h-[calc(100vh-57px)]">
          {/* Background logo watermark */}
          <div 
            className="fixed inset-0 pointer-events-none z-0"
            style={{
              backgroundImage: `url(${chedLogo})`,
              backgroundRepeat: 'no-repeat',
              backgroundPosition: 'center center',
              backgroundSize: '400px',
              opacity: 0.015,
              left: '256px'
            }}
          />
          <div className="relative z-10 p-4 sm:p-6 lg:p-8 max-w-[1600px] mx-auto">
            {children}
          </div>
        </main>
      </div>
    </div>
  );
}
