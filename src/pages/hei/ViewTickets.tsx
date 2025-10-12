import React, { useState } from 'react';
import { useAuth } from '../../context/AuthContext';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../components/ui/card';
import { Badge } from '../../components/ui/badge';
import { Button } from '../../components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../components/ui/table';
import { Switch } from '../../components/ui/switch';
import { Label } from '../../components/ui/label';
import { Eye, Download, Upload, Calendar, MessageSquare } from 'lucide-react';
import { mockTickets } from '../../data/mockData';
import { TicketStatus, TicketPriority } from '../../types';

interface ViewTicketsProps {
  onNavigate?: (page: string, data?: any) => void;
}

export function ViewTickets({ onNavigate }: ViewTicketsProps) {
  const { user } = useAuth();
  const [showOrgWide, setShowOrgWide] = useState(false);

  // Filter tickets based on user role and toggle
  const visibleTickets = mockTickets.filter(ticket => {
    if (showOrgWide && user?.role === 'HEI_HEAD') {
      return ticket.heiId === user.heiId;
    }
    return ticket.assigneeId === user?.id;
  });

  const getPriorityColor = (priority: TicketPriority) => {
    switch (priority) {
      case 'Urgent': return 'destructive';
      case 'High': return 'default';
      case 'Medium': return 'secondary';
      default: return 'outline';
    }
  };

  const getStatusColor = (status: TicketStatus) => {
    switch (status) {
      case 'Open': return 'default';
      case 'In Progress': return 'secondary';
      case 'Resolved': return 'outline';
      default: return 'outline';
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1>Tickets</h1>
          <p className="text-gray-600">Manage your assigned data submission tickets</p>
        </div>
        {user?.role === 'HEI_HEAD' && (
          <div className="flex items-center gap-2">
            <Switch
              id="org-wide"
              checked={showOrgWide}
              onCheckedChange={setShowOrgWide}
            />
            <Label htmlFor="org-wide">Show all organization tickets</Label>
          </div>
        )}
      </div>

      <Card>
        <CardHeader>
          <CardTitle>
            {showOrgWide ? 'Organization Tickets' : 'My Tickets'} ({visibleTickets.length})
          </CardTitle>
          <CardDescription>
            {showOrgWide 
              ? 'All tickets assigned to your institution'
              : 'Tickets assigned to you'
            }
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Ticket</TableHead>
                <TableHead>Category</TableHead>
                <TableHead>Assignee</TableHead>
                <TableHead>Priority</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Due Date</TableHead>
                <TableHead>Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {visibleTickets.map((ticket) => (
                <TableRow key={ticket.id}>
                  <TableCell>
                    <div className="max-w-xs">
                      <p className="truncate">{ticket.title}</p>
                      <p className="text-xs text-gray-500">
                        Created by {ticket.createdBy}
                      </p>
                    </div>
                  </TableCell>
                  <TableCell>
                    <Badge variant="outline">{ticket.category}</Badge>
                  </TableCell>
                  <TableCell>
                    <p className="text-sm">{ticket.assigneeName}</p>
                  </TableCell>
                  <TableCell>
                    <Badge variant={getPriorityColor(ticket.priority)}>
                      {ticket.priority}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <Badge variant={getStatusColor(ticket.status)}>
                      {ticket.status}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <div className="flex items-center gap-1 text-sm">
                      <Calendar className="w-3 h-3" />
                      {new Date(ticket.dueDate).toLocaleDateString()}
                    </div>
                  </TableCell>
                  <TableCell>
                    <div className="flex gap-1">
                      <Button 
                        variant="ghost" 
                        size="sm" 
                        title="View Details"
                        onClick={() => onNavigate?.('ticket-details', { ticketId: ticket.id })}
                      >
                        <Eye className="w-4 h-4" />
                      </Button>
                      <Button variant="ghost" size="sm" title="Download Template">
                        <Download className="w-4 h-4" />
                      </Button>
                      <Button variant="ghost" size="sm" title="Upload Data">
                        <Upload className="w-4 h-4" />
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              ))}
              {visibleTickets.length === 0 && (
                <TableRow>
                  <TableCell colSpan={7} className="text-center py-8 text-gray-500">
                    No tickets found
                  </TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      {/* Quick Info */}
      <div className="grid md:grid-cols-3 gap-4">
        <Card>
          <CardContent className="p-6">
            <div className="flex items-center gap-3">
              <div className="p-3 bg-orange-100 rounded-lg">
                <Calendar className="w-6 h-6 text-orange-600" />
              </div>
              <div>
                <p className="text-sm text-gray-600">Due This Week</p>
                <p className="text-2xl">
                  {visibleTickets.filter(t => {
                    const dueDate = new Date(t.dueDate);
                    const weekFromNow = new Date();
                    weekFromNow.setDate(weekFromNow.getDate() + 7);
                    return dueDate <= weekFromNow;
                  }).length}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-6">
            <div className="flex items-center gap-3">
              <div className="p-3 bg-blue-100 rounded-lg">
                <MessageSquare className="w-6 h-6 text-blue-600" />
              </div>
              <div>
                <p className="text-sm text-gray-600">Unread Comments</p>
                <p className="text-2xl">3</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-6">
            <div className="flex items-center gap-3">
              <div className="p-3 bg-green-100 rounded-lg">
                <Upload className="w-6 h-6 text-green-600" />
              </div>
              <div>
                <p className="text-sm text-gray-600">Pending Uploads</p>
                <p className="text-2xl">
                  {visibleTickets.filter(t => 
                    t.status === 'Open' || t.status === 'In Progress'
                  ).length}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
