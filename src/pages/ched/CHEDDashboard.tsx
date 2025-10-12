import React from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../components/ui/card';
import { Badge } from '../../components/ui/badge';
import { Button } from '../../components/ui/button';
import { Ticket, Building2, Users, TrendingUp, AlertCircle, CheckCircle } from 'lucide-react';
import { mockTickets, mockHEIs, mockETLJobs } from '../../data/mockData';

interface CHEDDashboardProps {
  onNavigate?: (page: string, data?: any) => void;
}

export function CHEDDashboard({ onNavigate }: CHEDDashboardProps) {
  const openTickets = mockTickets.filter(t => t.status === 'Open' || t.status === 'In Progress');
  const urgentTickets = mockTickets.filter(t => t.priority === 'Urgent' || t.priority === 'High');
  const recentETL = mockETLJobs.slice(0, 5);

  const stats = [
    { label: 'Total HEIs', value: mockHEIs.length, icon: Building2, color: 'bg-blue-500' },
    { label: 'Open Tickets', value: openTickets.length, icon: Ticket, color: 'bg-orange-500' },
    { label: 'Urgent Tickets', value: urgentTickets.length, icon: AlertCircle, color: 'bg-red-500' },
    { label: 'Active Users', value: '247', icon: Users, color: 'bg-green-500' },
  ];

  return (
    <div className="space-y-6">
      <div>
        <h1>CHED Dashboard</h1>
        <p className="text-gray-600">Overview of Higher Education Institutions and data submissions</p>
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
        {/* Recent Tickets */}
        <Card>
          <CardHeader>
            <CardTitle>Recent Tickets</CardTitle>
            <CardDescription>Latest ticket activity across institutions</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              {mockTickets.slice(0, 5).map((ticket) => (
                <div 
                  key={ticket.id} 
                  className="flex items-start gap-3 p-3 hover:bg-gray-50 rounded-lg cursor-pointer"
                  onClick={() => onNavigate?.('ticket-details', { ticketId: ticket.id })}
                >
                  <div className="flex-1 min-w-0">
                    <p className="text-sm truncate">{ticket.title}</p>
                    <p className="text-xs text-gray-500">{ticket.heiName}</p>
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

        {/* ETL Status */}
        <Card>
          <CardHeader>
            <CardTitle>Recent ETL Jobs</CardTitle>
            <CardDescription>Data ingestion and processing status</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              {recentETL.map((job) => (
                <div key={job.id} className="flex items-start gap-3 p-3 hover:bg-gray-50 rounded-lg">
                  <div className="mt-1">
                    {job.status === 'Success' ? (
                      <CheckCircle className="w-5 h-5 text-green-500" />
                    ) : (
                      <AlertCircle className="w-5 h-5 text-red-500" />
                    )}
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className="text-sm">{job.domain} - {job.heiName}</p>
                    <p className="text-xs text-gray-500">
                      {job.successRows} / {job.totalRows} rows processed
                    </p>
                  </div>
                  <Badge variant={job.status === 'Success' ? 'default' : 'destructive'}>
                    {job.status}
                  </Badge>
                </div>
              ))}
            </div>
            <Button 
              variant="outline" 
              className="w-full mt-4"
              onClick={() => onNavigate?.('etl-jobs')}
            >
              View All Jobs
            </Button>
          </CardContent>
        </Card>
      </div>

      {/* Quick Stats */}
      <Card>
        <CardHeader>
          <CardTitle>Data Updates Summary</CardTitle>
          <CardDescription>Recent data submissions across domains</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid md:grid-cols-3 gap-4">
            <div className="p-4 bg-blue-50 rounded-lg">
              <div className="flex items-center gap-2 mb-2">
                <TrendingUp className="w-5 h-5 text-blue-600" />
                <span className="text-sm text-blue-900">Enrollment</span>
              </div>
              <p className="text-2xl text-blue-900">1,245</p>
              <p className="text-xs text-blue-700">Records updated this week</p>
            </div>
            <div className="p-4 bg-green-50 rounded-lg">
              <div className="flex items-center gap-2 mb-2">
                <Users className="w-5 h-5 text-green-600" />
                <span className="text-sm text-green-900">Faculty</span>
              </div>
              <p className="text-2xl text-green-900">892</p>
              <p className="text-xs text-green-700">Records updated this week</p>
            </div>
            <div className="p-4 bg-purple-50 rounded-lg">
              <div className="flex items-center gap-2 mb-2">
                <TrendingUp className="w-5 h-5 text-purple-600" />
                <span className="text-sm text-purple-900">Graduates</span>
              </div>
              <p className="text-2xl text-purple-900">567</p>
              <p className="text-xs text-purple-700">Records updated this week</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
