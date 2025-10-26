import React from 'react';
import { useAuth } from '../../context/AuthContext';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../components/ui/card';
import { Badge } from '../../components/ui/badge';
import { Button } from '../../components/ui/button';
import { Ticket, AlertCircle, CheckCircle, Clock, Upload } from 'lucide-react';
import { mockTickets } from '../../data/mockData';

interface HEIDashboardProps {
  onNavigate?: (page: string, data?: any) => void;
}

export function HEIDashboard({ onNavigate }: HEIDashboardProps) {
  const { user } = useAuth();

  // Filter tickets for current user's HEI
  const heiTickets = mockTickets.filter(t => t.heiId === user?.heiId);
  const myTickets = heiTickets.filter(t => t.assigneeId === user?.id);
  const urgentTickets = myTickets.filter(t => t.priority === 'Urgent' || t.priority === 'High');
  const openTickets = myTickets.filter(t => t.status === 'Open' || t.status === 'In Progress');

  const stats = [
    { label: 'My Tickets', value: myTickets.length, icon: Ticket, color: 'bg-blue-500' },
    { label: 'Urgent', value: urgentTickets.length, icon: AlertCircle, color: 'bg-red-500' },
    { label: 'In Progress', value: openTickets.length, icon: Clock, color: 'bg-orange-500' },
    { label: 'Completed', value: myTickets.filter(t => t.status === 'Resolved').length, icon: CheckCircle, color: 'bg-green-500' },
  ];

  return (
    <div className="space-y-6">
      <div>
        <h1>Dashboard</h1>
        <p className="text-gray-600">Welcome back, {user?.firstName}!</p>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        {stats.map((stat) => {
          const Icon = stat.icon;
          return (
            <Card key={stat.label}>
              <CardContent className="p-6">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-sm text-gray-600">{stat.label}</p>
                    <p className="text-2xl mt-1">{stat.value}</p>
                  </div>
                  <div className={`${stat.color} p-3 rounded-lg`}>
                    <Icon className="w-6 h-6 text-white" />
                  </div>
                </div>
              </CardContent>
            </Card>
          );
        })}
      </div>

      <div className="grid lg:grid-cols-2 gap-6">
        {/* Assigned Tickets */}
        <Card>
          <CardHeader>
            <CardTitle>My Assigned Tickets</CardTitle>
            <CardDescription>Tickets assigned to you</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              {myTickets.slice(0, 5).map((ticket) => (
                <div 
                  key={ticket.id} 
                  className="flex items-start gap-3 p-3 hover:bg-gray-50 rounded-lg border cursor-pointer"
                  onClick={() => onNavigate?.('ticket-details', { ticketId: ticket.id })}
                >
                  <div className="flex-1 min-w-0">
                    <p className="text-sm truncate">{ticket.title}</p>
                    <p className="text-xs text-gray-500">Due: {new Date(ticket.dueDate).toLocaleDateString()}</p>
                  </div>
                  <div className="flex flex-col items-end gap-1">
                    <Badge variant={
                      ticket.priority === 'Urgent' ? 'destructive' :
                      ticket.priority === 'High' ? 'default' : 'secondary'
                    }>
                      {ticket.priority}
                    </Badge>
                    <Badge variant="outline" className="text-xs">
                      {ticket.status}
                    </Badge>
                  </div>
                </div>
              ))}
              {myTickets.length === 0 && (
                <div className="text-center py-8 text-gray-500">
                  <Ticket className="w-12 h-12 mx-auto mb-2 opacity-50" />
                  <p>No tickets assigned</p>
                </div>
              )}
            </div>
            <Button 
              variant="outline" 
              className="w-full mt-4"
              onClick={() => onNavigate?.('view-tickets')}
            >
              View All Tickets
            </Button>
          </CardContent>
        </Card>

        {/* Data Status */}
        <Card>
          <CardHeader>
            <CardTitle>Data Submission Status</CardTitle>
            <CardDescription>Recent data updates and ETL status</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              <div className="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200">
                <div className="flex items-center gap-3">
                  <CheckCircle className="w-5 h-5 text-green-600" />
                  <div>
                    <p className="text-sm">Enrollment Data</p>
                    <p className="text-xs text-gray-500">Last updated: Oct 11, 2025</p>
                  </div>
                </div>
                <Badge variant="default">Complete</Badge>
              </div>

              <div className="flex items-center justify-between p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                <div className="flex items-center gap-3">
                  <Clock className="w-5 h-5 text-yellow-600" />
                  <div>
                    <p className="text-sm">Faculty Data</p>
                    <p className="text-xs text-gray-500">Due: Oct 20, 2025</p>
                  </div>
                </div>
                <Badge variant="secondary">Pending</Badge>
              </div>

              <div className="flex items-center justify-between p-3 bg-orange-50 rounded-lg border border-orange-200">
                <div className="flex items-center gap-3">
                  <AlertCircle className="w-5 h-5 text-orange-600" />
                  <div>
                    <p className="text-sm">Graduates Data</p>
                    <p className="text-xs text-gray-500">Due: Oct 18, 2025</p>
                  </div>
                </div>
                <Badge variant="destructive">Urgent</Badge>
              </div>
            </div>
            <Button variant="outline" className="w-full mt-4">
              <Upload className="w-4 h-4 mr-2" />
              Upload Data
            </Button>
          </CardContent>
        </Card>
      </div>

      {/* Quick Actions */}
      <Card>
        <CardHeader>
          <CardTitle>Quick Actions</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid md:grid-cols-3 gap-4">
            <Button 
              variant="outline" 
              className="justify-start h-auto py-4"
              onClick={() => onNavigate?.('enrollment')}
            >
              <div className="text-left">
                <p className="text-sm">Upload Enrollment Data</p>
                <p className="text-xs text-gray-500">Submit Q1 2025 enrollment</p>
              </div>
            </Button>
            <Button 
              variant="outline" 
              className="justify-start h-auto py-4"
              onClick={() => onNavigate?.('faculty')}
            >
              <div className="text-left">
                <p className="text-sm">Update Faculty Records</p>
                <p className="text-xs text-gray-500">Add or modify faculty data</p>
              </div>
            </Button>
            <Button 
              variant="outline" 
              className="justify-start h-auto py-4"
              onClick={() => onNavigate?.('view-tickets')}
            >
              <div className="text-left">
                <p className="text-sm">View Reports</p>
                <p className="text-xs text-gray-500">Access submission history</p>
              </div>
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
