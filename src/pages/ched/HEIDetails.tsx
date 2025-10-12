import React, { useState } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../components/ui/card';
import { Button } from '../../components/ui/button';
import { Input } from '../../components/ui/input';
import { Label } from '../../components/ui/label';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../../components/ui/tabs';
import { Badge } from '../../components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../components/ui/table';
import { ArrowLeft, Save, Users, Ticket, FileText, Edit2, Mail, Phone, MapPin } from 'lucide-react';
import { mockHEIs, mockTickets } from '../../data/mockData';
import { toast } from 'sonner@2.0.3';

interface HEIDetailsProps {
  onNavigate?: (page: string) => void;
}

export function HEIDetails({ onNavigate }: HEIDetailsProps) {
  const hei = mockHEIs[0]; // Using first HEI as example
  const [isEditing, setIsEditing] = useState(false);
  const [formData, setFormData] = useState(hei);

  const heiTickets = mockTickets.filter(t => t.heiId === hei.id);
  
  // Mock users for this HEI
  const heiUsers = [
    { id: 'hei-h-1', name: 'Juan Dela Cruz', role: 'Head', email: 'jdelacruz@up.edu.ph', status: 'Active', lastLogin: '2025-10-11' },
    { id: 'hei-u-1', name: 'Ana Reyes', role: 'Sub-User', email: 'areyes@up.edu.ph', status: 'Active', lastLogin: '2025-10-10' },
    { id: 'hei-u-2', name: 'Pedro Santos', role: 'Sub-User', email: 'psantos@up.edu.ph', status: 'Active', lastLogin: '2025-10-09' },
  ];

  // Mock data summaries
  const dataSummaries = [
    { domain: 'Enrollment', lastUpdate: '2025-10-11', records: 1245, status: 'Complete' },
    { domain: 'Faculty', lastUpdate: '2025-09-28', records: 892, status: 'Pending' },
    { domain: 'Graduates', lastUpdate: '2025-09-15', records: 567, status: 'Incomplete' },
  ];

  const handleSave = () => {
    toast.success('Institution profile updated successfully');
    setIsEditing(false);
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-4">
          <Button 
            variant="ghost" 
            size="icon"
            onClick={() => onNavigate?.('view-heis')}
          >
            <ArrowLeft className="w-5 h-5" />
          </Button>
          <div>
            <h1>{hei.name}</h1>
            <p className="text-gray-600">{hei.shortName} • {hei.region}</p>
          </div>
        </div>
        <Badge variant={hei.status === 'Active' ? 'default' : 'secondary'}>
          {hei.status}
        </Badge>
      </div>

      <Tabs defaultValue="profile" className="space-y-6">
        <TabsList>
          <TabsTrigger value="profile">Institution Profile</TabsTrigger>
          <TabsTrigger value="users">Users ({heiUsers.length})</TabsTrigger>
          <TabsTrigger value="tickets">Tickets ({heiTickets.length})</TabsTrigger>
          <TabsTrigger value="data">Data Summaries</TabsTrigger>
        </TabsList>

        {/* Profile Tab */}
        <TabsContent value="profile" className="space-y-6">
          <div className="flex justify-end">
            {!isEditing ? (
              <Button onClick={() => setIsEditing(true)}>
                <Edit2 className="w-4 h-4 mr-2" />
                Edit Profile
              </Button>
            ) : (
              <div className="flex gap-2">
                <Button onClick={handleSave}>
                  <Save className="w-4 h-4 mr-2" />
                  Save Changes
                </Button>
                <Button variant="outline" onClick={() => setIsEditing(false)}>
                  Cancel
                </Button>
              </div>
            )}
          </div>

          <div className="grid lg:grid-cols-2 gap-6">
            <Card>
              <CardHeader>
                <CardTitle>Basic Information</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2">
                  <Label>Institution Name</Label>
                  {isEditing ? (
                    <Input value={formData.name} onChange={(e) => setFormData({...formData, name: e.target.value})} />
                  ) : (
                    <p>{formData.name}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label>Short Name</Label>
                  {isEditing ? (
                    <Input value={formData.shortName} onChange={(e) => setFormData({...formData, shortName: e.target.value})} />
                  ) : (
                    <p>{formData.shortName}</p>
                  )}
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label>Institution Type</Label>
                    <p>{formData.institutionType}</p>
                  </div>
                  <div className="space-y-2">
                    <Label>Ownership</Label>
                    <p>{formData.ownershipForm}</p>
                  </div>
                </div>

                <div className="space-y-2">
                  <Label>Region</Label>
                  <p>{formData.region}</p>
                </div>

                <div className="space-y-2">
                  <Label>Municipality/City</Label>
                  {isEditing ? (
                    <Input value={formData.municipality} onChange={(e) => setFormData({...formData, municipality: e.target.value})} />
                  ) : (
                    <p>{formData.municipality}</p>
                  )}
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Contact Information</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2">
                  <Label>Address</Label>
                  {isEditing ? (
                    <Input value={formData.address} onChange={(e) => setFormData({...formData, address: e.target.value})} />
                  ) : (
                    <div className="flex items-start gap-2">
                      <MapPin className="w-4 h-4 text-gray-500 mt-1" />
                      <p>{formData.address}</p>
                    </div>
                  )}
                </div>

                <div className="space-y-2">
                  <Label>Email</Label>
                  {isEditing ? (
                    <Input type="email" value={formData.email} onChange={(e) => setFormData({...formData, email: e.target.value})} />
                  ) : (
                    <div className="flex items-center gap-2">
                      <Mail className="w-4 h-4 text-gray-500" />
                      <p>{formData.email}</p>
                    </div>
                  )}
                </div>

                <div className="space-y-2">
                  <Label>Phone</Label>
                  {isEditing ? (
                    <Input value={formData.phone} onChange={(e) => setFormData({...formData, phone: e.target.value})} />
                  ) : (
                    <div className="flex items-center gap-2">
                      <Phone className="w-4 h-4 text-gray-500" />
                      <p>{formData.phone}</p>
                    </div>
                  )}
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Institution Head</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2">
                  <Label>Head Name</Label>
                  {isEditing ? (
                    <Input value={formData.headName} onChange={(e) => setFormData({...formData, headName: e.target.value})} />
                  ) : (
                    <p>{formData.headName}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label>Title</Label>
                  {isEditing ? (
                    <Input value={formData.headTitle} onChange={(e) => setFormData({...formData, headTitle: e.target.value})} />
                  ) : (
                    <p>{formData.headTitle}</p>
                  )}
                </div>
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        {/* Users Tab */}
        <TabsContent value="users">
          <Card>
            <CardHeader>
              <div className="flex items-center justify-between">
                <div>
                  <CardTitle>Organization Users</CardTitle>
                  <CardDescription>User accounts for this institution</CardDescription>
                </div>
                <Button>
                  <Users className="w-4 h-4 mr-2" />
                  Add User
                </Button>
              </div>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Name</TableHead>
                    <TableHead>Email</TableHead>
                    <TableHead>Role</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Last Login</TableHead>
                    <TableHead>Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {heiUsers.map(user => (
                    <TableRow key={user.id}>
                      <TableCell>{user.name}</TableCell>
                      <TableCell>{user.email}</TableCell>
                      <TableCell>
                        <Badge variant="outline">{user.role}</Badge>
                      </TableCell>
                      <TableCell>
                        <Badge variant="default">{user.status}</Badge>
                      </TableCell>
                      <TableCell>{new Date(user.lastLogin).toLocaleDateString()}</TableCell>
                      <TableCell>
                        <Button variant="ghost" size="sm">Edit</Button>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>

        {/* Tickets Tab */}
        <TabsContent value="tickets">
          <Card>
            <CardHeader>
              <CardTitle>Organization Tickets</CardTitle>
              <CardDescription>Data submission tickets for this institution</CardDescription>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Title</TableHead>
                    <TableHead>Category</TableHead>
                    <TableHead>Assignee</TableHead>
                    <TableHead>Priority</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Due Date</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {heiTickets.map(ticket => (
                    <TableRow key={ticket.id}>
                      <TableCell className="max-w-xs truncate">{ticket.title}</TableCell>
                      <TableCell><Badge variant="outline">{ticket.category}</Badge></TableCell>
                      <TableCell>{ticket.assigneeName}</TableCell>
                      <TableCell>
                        <Badge variant={ticket.priority === 'High' ? 'default' : 'secondary'}>
                          {ticket.priority}
                        </Badge>
                      </TableCell>
                      <TableCell><Badge variant="outline">{ticket.status}</Badge></TableCell>
                      <TableCell>{new Date(ticket.dueDate).toLocaleDateString()}</TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>

        {/* Data Summaries Tab */}
        <TabsContent value="data">
          <Card>
            <CardHeader>
              <CardTitle>Data Submission Summary</CardTitle>
              <CardDescription>Overview of data submissions by domain</CardDescription>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Domain</TableHead>
                    <TableHead>Last Update</TableHead>
                    <TableHead>Total Records</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {dataSummaries.map((summary, index) => (
                    <TableRow key={index}>
                      <TableCell>
                        <div className="flex items-center gap-2">
                          <FileText className="w-4 h-4 text-gray-500" />
                          {summary.domain}
                        </div>
                      </TableCell>
                      <TableCell>{new Date(summary.lastUpdate).toLocaleDateString()}</TableCell>
                      <TableCell>{summary.records.toLocaleString()}</TableCell>
                      <TableCell>
                        <Badge variant={
                          summary.status === 'Complete' ? 'default' :
                          summary.status === 'Pending' ? 'secondary' : 'destructive'
                        }>
                          {summary.status}
                        </Badge>
                      </TableCell>
                      <TableCell>
                        <Button variant="ghost" size="sm">View Data</Button>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  );
}
