import React, { useState } from 'react';
import { AuthProvider, useAuth } from './context/AuthContext';
import { Login } from './components/Login';
import { CHEDLayout } from './components/CHEDLayout';
import { HEILayout } from './components/HEILayout';
import { Toaster } from './components/ui/sonner';

// CHED Pages
import { CHEDDashboard } from './pages/ched/CHEDDashboard';
import { ViewHEIs } from './pages/ched/ViewHEIs';
import { ViewTickets as CHEDViewTickets } from './pages/ched/ViewTickets';
import { DataEnrollment } from './pages/ched/DataEnrollment';
import { EnrollmentDetails } from './pages/ched/EnrollmentDetails';
import { ETLJobs } from './pages/ched/ETLJobs';
import { HEIAnalytics } from './pages/ched/HEIAnalytics';
import { CreateTicket } from './pages/ched/CreateTicket';
import { HEIDetails } from './pages/ched/HEIDetails';
import { CodeTables } from './pages/ched/CodeTables';

// HEI Pages
import { HEIDashboard } from './pages/hei/HEIDashboard';
import { ViewTickets as HEIViewTickets } from './pages/hei/ViewTickets';
import { EnrollmentDataEntry } from './pages/hei/EnrollmentDataEntry';
import { FacultyDataEntry } from './pages/hei/FacultyDataEntry';
import { InstitutionProfile } from './pages/hei/InstitutionProfile';

// Placeholder components for unimplemented pages
import { Card, CardContent, CardHeader, CardTitle } from './components/ui/card';

function PlaceholderPage({ title, description }: { title: string; description: string }) {
  return (
    <div className="space-y-6">
      <div>
        <h1>{title}</h1>
        <p className="text-gray-600">{description}</p>
      </div>
      <Card>
        <CardHeader>
          <CardTitle>Coming Soon</CardTitle>
        </CardHeader>
        <CardContent>
          <p className="text-gray-600">This page is under development.</p>
        </CardContent>
      </Card>
    </div>
  );
}

function AppContent() {
  const { user, isAuthenticated } = useAuth();
  const [currentPage, setCurrentPage] = useState('dashboard');
  const [pageData, setPageData] = useState<any>(null);

  const handleNavigation = (page: string, data?: any) => {
    setCurrentPage(page);
    setPageData(data || null);
  };

  if (!isAuthenticated) {
    return <Login />;
  }

  // CHED User Routes
  if (user?.role === 'CHED') {
    const renderCHEDPage = () => {
      switch (currentPage) {
        case 'dashboard':
          return <CHEDDashboard onNavigate={handleNavigation} />;
        case 'view-heis':
          return <ViewHEIs onNavigate={handleNavigation} />;
        case 'hei-details':
          return <HEIDetails onNavigate={handleNavigation} />;
        case 'view-tickets':
          return <CHEDViewTickets onNavigate={handleNavigation} />;
        case 'create-ticket':
          return <CreateTicket onNavigate={handleNavigation} />;
        case 'ticket-details':
          return <PlaceholderPage title="Ticket Details" description="View and manage ticket details" />;
        case 'data-enrollment':
          return <DataEnrollment onNavigate={handleNavigation} />;
        case 'enrollment-details':
          return <EnrollmentDetails onNavigate={handleNavigation} heiData={pageData} />;
        case 'data-faculty':
          return <PlaceholderPage title="Faculty Data" description="Browse and export faculty records across all HEIs" />;
        case 'data-graduates':
          return <PlaceholderPage title="Graduates Data" description="Browse and export graduate records across all HEIs" />;
        case 'manage-data-templates':
          return <PlaceholderPage title="Templates & Mappings" description="Manage data templates and ETL mappings" />;
        case 'etl-jobs':
          return <ETLJobs />;
        case 'code-tables':
          return <CodeTables />;
        case 'hei-analytics':
          return <HEIAnalytics />;
        default:
          return <CHEDDashboard onNavigate={handleNavigation} />;
      }
    };

    return (
      <CHEDLayout currentPage={currentPage} onNavigate={handleNavigation}>
        {renderCHEDPage()}
      </CHEDLayout>
    );
  }

  // HEI User Routes (Head and Sub-User)
  if (user?.role === 'HEI_HEAD' || user?.role === 'HEI_SUB_USER') {
    const renderHEIPage = () => {
      switch (currentPage) {
        case 'dashboard':
          return <HEIDashboard onNavigate={handleNavigation} />;
        case 'view-tickets':
          return <HEIViewTickets onNavigate={handleNavigation} />;
        case 'ticket-details':
          return <PlaceholderPage title="Ticket Details" description="View and manage ticket details" />;
        case 'enrollment':
          return <EnrollmentDataEntry />;
        case 'faculty':
          return <FacultyDataEntry />;
        case 'graduates':
          return <PlaceholderPage title="Graduates Data Entry" description="Manage graduate records for your institution" />;
        case 'institution-profile':
          return <InstitutionProfile />;
        case 'users':
          return <PlaceholderPage title="Sub-Users Management" description="Manage sub-user accounts for your institution" />;
        case 'set-calendar':
          return <PlaceholderPage title="Calendar" description="Manage events and view ticket due dates" />;
        default:
          return <HEIDashboard onNavigate={handleNavigation} />;
      }
    };

    return (
      <HEILayout currentPage={currentPage} onNavigate={handleNavigation}>
        {renderHEIPage()}
      </HEILayout>
    );
  }

  return <Login />;
}

export default function App() {
  return (
    <AuthProvider>
      <AppContent />
      <Toaster />
    </AuthProvider>
  );
}
