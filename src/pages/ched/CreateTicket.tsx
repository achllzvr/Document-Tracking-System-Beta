import React, { useState } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../components/ui/card';
import { Button } from '../../components/ui/button';
import { Input } from '../../components/ui/input';
import { Label } from '../../components/ui/label';
import { Textarea } from '../../components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../components/ui/select';
import { ArrowLeft, Save } from 'lucide-react';
import { mockHEIs } from '../../data/mockData';
import { toast } from 'sonner@2.0.3';

interface CreateTicketProps {
  onNavigate?: (page: string) => void;
}

export function CreateTicket({ onNavigate }: CreateTicketProps) {
  const [formData, setFormData] = useState({
    heiId: '',
    title: '',
    category: '',
    priority: 'Medium',
    assigneeId: '',
    dueDate: '',
    description: '',
    status: 'Open'
  });

  const [selectedHEI, setSelectedHEI] = useState<string>('');

  // Mock HEI users - in real app, would fetch based on selected HEI
  const heiUsers = [
    { id: 'hei-h-1', name: 'Juan Dela Cruz', role: 'Head' },
    { id: 'hei-u-1', name: 'Ana Reyes', role: 'Sub-User' },
    { id: 'hei-u-2', name: 'Pedro Santos', role: 'Sub-User' },
  ];

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!formData.heiId || !formData.title || !formData.category || !formData.assigneeId || !formData.dueDate) {
      toast.error('Please fill in all required fields');
      return;
    }

    toast.success('Ticket created successfully');
    // Navigate back to tickets list
    setTimeout(() => {
      onNavigate?.('view-tickets');
    }, 1000);
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-4">
        <Button 
          variant="ghost" 
          size="icon"
          onClick={() => onNavigate?.('view-tickets')}
        >
          <ArrowLeft className="w-5 h-5" />
        </Button>
        <div>
          <h1>Create New Ticket</h1>
          <p className="text-gray-600">Create a data submission ticket for an HEI</p>
        </div>
      </div>

      <form onSubmit={handleSubmit}>
        <div className="grid lg:grid-cols-3 gap-6">
          {/* Main Form */}
          <div className="lg:col-span-2 space-y-6">
            <Card>
              <CardHeader>
                <CardTitle>Ticket Information</CardTitle>
                <CardDescription>Basic details about the ticket</CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="hei">Higher Education Institution *</Label>
                  <Select 
                    value={selectedHEI}
                    onValueChange={(value) => {
                      setSelectedHEI(value);
                      setFormData({...formData, heiId: value});
                    }}
                  >
                    <SelectTrigger id="hei">
                      <SelectValue placeholder="Select HEI" />
                    </SelectTrigger>
                    <SelectContent>
                      {mockHEIs.map(hei => (
                        <SelectItem key={hei.id} value={hei.id}>
                          {hei.name} ({hei.shortName})
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="title">Ticket Title *</Label>
                  <Input
                    id="title"
                    value={formData.title}
                    onChange={(e) => setFormData({...formData, title: e.target.value})}
                    placeholder="e.g., Q1 2025 Enrollment Data Submission"
                    required
                  />
                </div>

                <div className="grid md:grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="category">Category *</Label>
                    <Select 
                      value={formData.category}
                      onValueChange={(value) => setFormData({...formData, category: value})}
                    >
                      <SelectTrigger id="category">
                        <SelectValue placeholder="Select category" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="Enrollment">Enrollment</SelectItem>
                        <SelectItem value="Faculty">Faculty</SelectItem>
                        <SelectItem value="Graduates">Graduates</SelectItem>
                        <SelectItem value="Institutional Profile">Institutional Profile</SelectItem>
                        <SelectItem value="Other">Other</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="priority">Priority *</Label>
                    <Select 
                      value={formData.priority}
                      onValueChange={(value) => setFormData({...formData, priority: value})}
                    >
                      <SelectTrigger id="priority">
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="Low">Low</SelectItem>
                        <SelectItem value="Medium">Medium</SelectItem>
                        <SelectItem value="High">High</SelectItem>
                        <SelectItem value="Urgent">Urgent</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="description">Description *</Label>
                  <Textarea
                    id="description"
                    value={formData.description}
                    onChange={(e) => setFormData({...formData, description: e.target.value})}
                    placeholder="Provide detailed instructions for the HEI..."
                    rows={5}
                    required
                  />
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Assignment</CardTitle>
                <CardDescription>Assign ticket to an HEI user</CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid md:grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="assignee">Assignee *</Label>
                    <Select 
                      value={formData.assigneeId}
                      onValueChange={(value) => setFormData({...formData, assigneeId: value})}
                      disabled={!selectedHEI}
                    >
                      <SelectTrigger id="assignee">
                        <SelectValue placeholder={selectedHEI ? "Select user" : "Select HEI first"} />
                      </SelectTrigger>
                      <SelectContent>
                        {heiUsers.map(user => (
                          <SelectItem key={user.id} value={user.id}>
                            {user.name} ({user.role})
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="dueDate">Due Date *</Label>
                    <Input
                      id="dueDate"
                      type="date"
                      value={formData.dueDate}
                      onChange={(e) => setFormData({...formData, dueDate: e.target.value})}
                      required
                    />
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Sidebar */}
          <div className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle>Actions</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                <Button type="submit" className="w-full">
                  <Save className="w-4 h-4 mr-2" />
                  Create Ticket
                </Button>
                <Button type="button" variant="outline" className="w-full">
                  Save as Draft
                </Button>
                <Button 
                  type="button" 
                  variant="ghost" 
                  className="w-full"
                  onClick={() => onNavigate?.('view-tickets')}
                >
                  Cancel
                </Button>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Ticket Status</CardTitle>
              </CardHeader>
              <CardContent className="space-y-2">
                <div className="space-y-2">
                  <Label htmlFor="status">Initial Status</Label>
                  <Select 
                    value={formData.status}
                    onValueChange={(value) => setFormData({...formData, status: value})}
                  >
                    <SelectTrigger id="status">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="Open">Open</SelectItem>
                      <SelectItem value="In Progress">In Progress</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Help</CardTitle>
              </CardHeader>
              <CardContent className="text-sm text-gray-600 space-y-2">
                <p>• Select the HEI before assigning users</p>
                <p>• Category determines which template will be available</p>
                <p>• Set realistic due dates for submissions</p>
                <p>• Use clear, detailed descriptions</p>
              </CardContent>
            </Card>
          </div>
        </div>
      </form>
    </div>
  );
}
