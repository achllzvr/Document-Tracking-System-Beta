import React, { useState } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../components/ui/card';
import { Badge } from '../../components/ui/badge';
import { Button } from '../../components/ui/button';
import { Textarea } from '../../components/ui/textarea';
import { Separator } from '../../components/ui/separator';
import { Calendar, User, Tag, MessageSquare, Send, Download, Upload, FileText, CheckCircle, AlertCircle } from 'lucide-react';
import { mockTickets } from '../../data/mockData';
import { toast } from 'sonner@2.0.3';

export function TicketDetails() {
  const ticket = mockTickets[0]; // Using first ticket as example
  const [newComment, setNewComment] = useState('');
  const [uploadedFile, setUploadedFile] = useState<File | null>(null);

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
    }
  ];

  const handlePostComment = () => {
    if (!newComment.trim()) return;
    toast.success('Comment posted successfully');
    setNewComment('');
  };

  const handleDownloadTemplate = () => {
    toast.success('Template downloaded successfully');
  };

  const handleFileUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      setUploadedFile(file);
      toast.success(`File "${file.name}" ready for upload`);
    }
  };

  const handleSubmitData = () => {
    if (!uploadedFile) {
      toast.error('Please select a file to upload');
      return;
    }
    toast.success('Data uploaded successfully. ETL processing started.');
    setUploadedFile(null);
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
      </div>

      <div className="grid lg:grid-cols-3 gap-6">
        {/* Main Content */}
        <div className="lg:col-span-2 space-y-6">
          {/* Ticket Information */}
          <Card>
            <CardHeader>
              <CardTitle>{ticket.title}</CardTitle>
              <CardDescription>
                Created by {ticket.createdBy} on {new Date(ticket.createdAt).toLocaleDateString()}
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div>
                <p className="text-sm text-gray-600 mb-2">Description</p>
                <p>{ticket.description}</p>
              </div>

              <Separator />

              <div className="grid md:grid-cols-2 gap-4 text-sm">
                <div className="flex items-center gap-2">
                  <User className="w-4 h-4 text-gray-500" />
                  <span className="text-gray-600">Assigned to:</span>
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
                <div className="flex items-center gap-2">
                  <FileText className="w-4 h-4 text-gray-500" />
                  <span className="text-gray-600">Status:</span>
                  <Badge variant="outline">{ticket.status}</Badge>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Upload Data Section */}
          <Card>
            <CardHeader>
              <CardTitle>Data Submission</CardTitle>
              <CardDescription>Download template and upload your completed data</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex gap-3">
                <Button variant="outline" className="flex-1" onClick={handleDownloadTemplate}>
                  <Download className="w-4 h-4 mr-2" />
                  Download {ticket.category} Template
                </Button>
              </div>

              <Separator />

              <div className="space-y-3">
                <div>
                  <label htmlFor="file-upload" className="block text-sm mb-2">
                    Upload Completed Template
                  </label>
                  <input
                    id="file-upload"
                    type="file"
                    accept=".csv,.xlsx,.xls"
                    onChange={handleFileUpload}
                    className="block w-full text-sm text-gray-500
                      file:mr-4 file:py-2 file:px-4
                      file:rounded-lg file:border-0
                      file:text-sm
                      file:bg-blue-50 file:text-blue-700
                      hover:file:bg-blue-100
                      cursor-pointer"
                  />
                  {uploadedFile && (
                    <p className="text-sm text-green-600 mt-2">
                      Selected: {uploadedFile.name}
                    </p>
                  )}
                </div>

                <Button 
                  onClick={handleSubmitData} 
                  disabled={!uploadedFile}
                  className="w-full"
                >
                  <Upload className="w-4 h-4 mr-2" />
                  Submit Data
                </Button>
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
                    <div className="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white flex-shrink-0">
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
              <CardTitle>Ticket Info</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
              <div className="flex items-center justify-between">
                <span className="text-sm text-gray-600">Status</span>
                <Badge variant="outline">{ticket.status}</Badge>
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
                  <span className="text-gray-600">Due Date:</span>
                  <span>{new Date(ticket.dueDate).toLocaleDateString()}</span>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* ETL Status */}
          <Card>
            <CardHeader>
              <CardTitle>Upload History</CardTitle>
              <CardDescription>Recent data submissions</CardDescription>
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
            </CardContent>
          </Card>

          {/* Quick Actions */}
          <Card>
            <CardHeader>
              <CardTitle>Quick Actions</CardTitle>
            </CardHeader>
            <CardContent className="space-y-2">
              <Button variant="outline" className="w-full justify-start" onClick={handleDownloadTemplate}>
                <Download className="w-4 h-4 mr-2" />
                Download Template
              </Button>
              <Button variant="outline" className="w-full justify-start">
                <FileText className="w-4 h-4 mr-2" />
                View Instructions
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}
