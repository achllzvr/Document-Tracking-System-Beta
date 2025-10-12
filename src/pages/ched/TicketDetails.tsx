import React, { useState } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../components/ui/card';
import { Badge } from '../../components/ui/badge';
import { Button } from '../../components/ui/button';
import { Textarea } from '../../components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../components/ui/select';
import { Separator } from '../../components/ui/separator';
import { Calendar, User, Building2, Tag, AlertCircle, MessageSquare, Send, CheckCircle } from 'lucide-react';
import { mockTickets } from '../../data/mockData';
import { TicketStatus } from '../../types';
import { toast } from 'sonner@2.0.3';

export function TicketDetails() {
  const ticket = mockTickets[0]; // Using first ticket as example
  const [newComment, setNewComment] = useState('');
  const [status, setStatus] = useState<TicketStatus>(ticket.status);

  const comments = [
    {
      id: 1,
      author: 'Maria Santos',
      role: 'CHED Admin',
      content: 'Please submit the enrollment data using the latest template. Make sure all required fields are filled.',
      timestamp: '2025-10-01 09:15 AM',
      avatar: 'MS'
    },
    {
      id: 2,
      author: 'Juan Dela Cruz',
      role: 'HEI Head',
      content: 'Acknowledged. We will compile the data and submit by end of week.',
      timestamp: '2025-10-01 11:30 AM',
      avatar: 'JD'
    },
    {
      id: 3,
      author: 'Ana Reyes',
      role: 'HEI Sub-User',
      content: 'Data has been uploaded successfully. Please review.',
      timestamp: '2025-10-11 10:30 AM',
      avatar: 'AR'
    }
  ];

  const handlePostComment = () => {
    if (!newComment.trim()) return;
    toast.success('Comment posted successfully');
    setNewComment('');
  };

  const handleStatusChange = (newStatus: string) => {
    setStatus(newStatus as TicketStatus);
    toast.success(`Ticket status updated to ${newStatus}`);
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <div className="flex items-center gap-2 mb-2">
            <h1>Ticket Details</h1>
            <Badge variant={
              ticket.priority === 'Urgent' ? 'destructive' :
              ticket.priority === 'High' ? 'default' : 'secondary'
            }>
              {ticket.priority}
            </Badge>
          </div>
          <p className="text-gray-600">ID: {ticket.id}</p>
        </div>
        <div className="flex gap-2">
          <Select value={status} onValueChange={handleStatusChange}>
            <SelectTrigger className="w-48">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="Open">Open</SelectItem>
              <SelectItem value="In Progress">In Progress</SelectItem>
              <SelectItem value="Pending Review">Pending Review</SelectItem>
              <SelectItem value="Resolved">Resolved</SelectItem>
              <SelectItem value="Closed">Closed</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>

      <div className="grid lg:grid-cols-3 gap-6">
        {/* Main Content */}
        <div className="lg:col-span-2 space-y-6">
          {/* Ticket Information */}
          <Card>
            <CardHeader>
              <CardTitle>{ticket.title}</CardTitle>
              <CardDescription>Created by {ticket.createdBy} on {new Date(ticket.createdAt).toLocaleDateString()}</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div>
                <p className="text-sm text-gray-600 mb-2">Description</p>
                <p>{ticket.description}</p>
              </div>

              <Separator />

              <div className="grid md:grid-cols-2 gap-4 text-sm">
                <div className="flex items-center gap-2">
                  <Building2 className="w-4 h-4 text-gray-500" />
                  <span className="text-gray-600">HEI:</span>
                  <span>{ticket.heiName}</span>
                </div>
                <div className="flex items-center gap-2">
                  <User className="w-4 h-4 text-gray-500" />
                  <span className="text-gray-600">Assignee:</span>
                  <span>{ticket.assigneeName}</span>
                </div>
                <div className="flex items-center gap-2">
                  <Tag className="w-4 h-4 text-gray-500" />
                  <span className="text-gray-600">Category:</span>
                  <Badge variant="outline">{ticket.category}</Badge>
                </div>
                <div className="flex items-center gap-2">
                  <Calendar className="w-4 h-4 text-gray-500" />
                  <span className="text-gray-600">Due Date:</span>
                  <span>{new Date(ticket.dueDate).toLocaleDateString()}</span>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Comments Section */}
          <Card>
            <CardHeader>
              <div className="flex items-center gap-2">
                <MessageSquare className="w-5 h-5" />
                <CardTitle>Comments</CardTitle>
                <Badge variant="secondary">{comments.length}</Badge>
              </div>
            </CardHeader>
            <CardContent className="space-y-4">
              {/* Existing Comments */}
              <div className="space-y-4">
                {comments.map((comment) => (
                  <div key={comment.id} className="flex gap-3">
                    <div className="w-10 h-10 rounded-full flex items-center justify-center text-white flex-shrink-0" style={{ background: 'linear-gradient(135deg, var(--ph-blue) 0%, var(--ph-blue-light) 100%)' }}>
                      {comment.avatar}
                    </div>
                    <div className="flex-1">
                      <div className="flex items-center gap-2 mb-1">
                        <span className="text-sm">{comment.author}</span>
                        <Badge variant="outline" className="text-xs">{comment.role}</Badge>
                        <span className="text-xs text-gray-500">{comment.timestamp}</span>
                      </div>
                      <p className="text-sm text-gray-700">{comment.content}</p>
                    </div>
                  </div>
                ))}
              </div>

              <Separator />

              {/* New Comment */}
              <div className="space-y-3">
                <Textarea
                  placeholder="Write a comment..."
                  value={newComment}
                  onChange={(e) => setNewComment(e.target.value)}
                  rows={3}
                />
                <div className="flex justify-end">
                  <Button onClick={handlePostComment}>
                    <Send className="w-4 h-4 mr-2" />
                    Post Comment
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Sidebar */}
        <div className="space-y-6">
          {/* Status Panel */}
          <Card>
            <CardHeader>
              <CardTitle>Status</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
              <div className="flex items-center justify-between">
                <span className="text-sm text-gray-600">Current Status</span>
                <Badge variant={
                  status === 'Open' ? 'default' :
                  status === 'In Progress' ? 'secondary' :
                  status === 'Resolved' ? 'outline' : 'default'
                }>
                  {status}
                </Badge>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-sm text-gray-600">Priority</span>
                <Badge variant={
                  ticket.priority === 'Urgent' ? 'destructive' :
                  ticket.priority === 'High' ? 'default' : 'secondary'
                }>
                  {ticket.priority}
                </Badge>
              </div>
              <Separator />
              <div className="space-y-2 text-sm">
                <div className="flex justify-between">
                  <span className="text-gray-600">Created:</span>
                  <span>{new Date(ticket.createdAt).toLocaleDateString()}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Updated:</span>
                  <span>{new Date(ticket.updatedAt).toLocaleDateString()}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Due Date:</span>
                  <span>{new Date(ticket.dueDate).toLocaleDateString()}</span>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Data Status */}
          <Card>
            <CardHeader>
              <CardTitle>Data Status</CardTitle>
              <CardDescription>ETL and submission status</CardDescription>
            </CardHeader>
            <CardContent className="space-y-3">
              <div className="flex items-start gap-2 p-3 bg-green-50 rounded-lg border border-green-200">
                <CheckCircle className="w-5 h-5 text-green-600 mt-0.5" />
                <div className="flex-1">
                  <p className="text-sm">Upload Successful</p>
                  <p className="text-xs text-gray-600">150 records processed</p>
                  <p className="text-xs text-gray-500 mt-1">Oct 11, 2025 10:30 AM</p>
                </div>
              </div>
              
              <Button variant="outline" className="w-full" size="sm">
                View ETL Details
              </Button>
            </CardContent>
          </Card>

          {/* Actions */}
          <Card>
            <CardHeader>
              <CardTitle>Actions</CardTitle>
            </CardHeader>
            <CardContent className="space-y-2">
              <Button variant="outline" className="w-full justify-start">
                <User className="w-4 h-4 mr-2" />
                Reassign Ticket
              </Button>
              <Button variant="outline" className="w-full justify-start">
                <Calendar className="w-4 h-4 mr-2" />
                Change Due Date
              </Button>
              <Button variant="outline" className="w-full justify-start">
                <AlertCircle className="w-4 h-4 mr-2" />
                Set Priority
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}
