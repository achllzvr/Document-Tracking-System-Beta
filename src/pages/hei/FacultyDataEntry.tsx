import React, { useState } from 'react';
import { useAuth } from '../../context/AuthContext';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../components/ui/card';
import { Button } from '../../components/ui/button';
import { Input } from '../../components/ui/input';
import { Label } from '../../components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../components/ui/table';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '../../components/ui/dialog';
import { Plus, Edit, Trash2, Save, Users } from 'lucide-react';
import { mockEmploymentTypes, mockDegrees, mockDisciplines } from '../../data/mockData';
import { toast } from 'sonner@2.0.3';

export function FacultyDataEntry() {
  const { user } = useAuth();
  const [isDialogOpen, setIsDialogOpen] = useState(false);
  const [formData, setFormData] = useState({
    name: '',
    employmentType: '',
    degree: '',
    discipline: '',
    sex: 'Male'
  });

  const [facultyRecords, setFacultyRecords] = useState([
    { id: 1, name: 'Dr. Jose Rizal', employmentType: 'Full-time', degree: 'PhD', discipline: 'Computer Science', sex: 'Male' },
    { id: 2, name: 'Dr. Maria Clara', employmentType: 'Full-time', degree: 'PhD', discipline: 'Engineering', sex: 'Female' },
    { id: 3, name: 'Prof. Juan Luna', employmentType: 'Part-time', degree: 'Masters', discipline: 'Business', sex: 'Male' },
    { id: 4, name: 'Dr. Gabriela Silang', employmentType: 'Full-time', degree: 'PhD', discipline: 'Education', sex: 'Female' },
  ]);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!formData.name || !formData.employmentType || !formData.degree || !formData.discipline) {
      toast.error('Please fill in all required fields');
      return;
    }

    const newRecord = {
      id: facultyRecords.length + 1,
      ...formData
    };
    
    setFacultyRecords([...facultyRecords, newRecord]);
    setIsDialogOpen(false);
    setFormData({
      name: '',
      employmentType: '',
      degree: '',
      discipline: '',
      sex: 'Male'
    });
    toast.success('Faculty record added successfully');
  };

  const handleDelete = (id: number) => {
    setFacultyRecords(facultyRecords.filter(r => r.id !== id));
    toast.success('Record deleted successfully');
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1>Faculty Data Entry</h1>
          <p className="text-gray-600">Manage faculty records for {user?.heiName}</p>
        </div>
        
        <Dialog open={isDialogOpen} onOpenChange={setIsDialogOpen}>
          <DialogTrigger asChild>
            <Button>
              <Plus className="w-4 h-4 mr-2" />
              Add Faculty
            </Button>
          </DialogTrigger>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Add Faculty Member</DialogTitle>
              <DialogDescription>
                Enter faculty member information
              </DialogDescription>
            </DialogHeader>
            <form onSubmit={handleSubmit}>
              <div className="space-y-4 py-4">
                <div className="space-y-2">
                  <Label htmlFor="name">Full Name *</Label>
                  <Input
                    id="name"
                    value={formData.name}
                    onChange={(e) => setFormData({...formData, name: e.target.value})}
                    placeholder="e.g., Dr. Juan Dela Cruz"
                    required
                  />
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="employmentType">Employment Type *</Label>
                    <Select 
                      value={formData.employmentType}
                      onValueChange={(value) => setFormData({...formData, employmentType: value})}
                    >
                      <SelectTrigger id="employmentType">
                        <SelectValue placeholder="Select type" />
                      </SelectTrigger>
                      <SelectContent>
                        {mockEmploymentTypes.map(type => (
                          <SelectItem key={type} value={type}>{type}</SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="sex">Sex *</Label>
                    <Select 
                      value={formData.sex}
                      onValueChange={(value) => setFormData({...formData, sex: value})}
                    >
                      <SelectTrigger id="sex">
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="Male">Male</SelectItem>
                        <SelectItem value="Female">Female</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="degree">Highest Degree *</Label>
                  <Select 
                    value={formData.degree}
                    onValueChange={(value) => setFormData({...formData, degree: value})}
                  >
                    <SelectTrigger id="degree">
                      <SelectValue placeholder="Select degree" />
                    </SelectTrigger>
                    <SelectContent>
                      {mockDegrees.map(degree => (
                        <SelectItem key={degree} value={degree}>{degree}</SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="discipline">Discipline *</Label>
                  <Select 
                    value={formData.discipline}
                    onValueChange={(value) => setFormData({...formData, discipline: value})}
                  >
                    <SelectTrigger id="discipline">
                      <SelectValue placeholder="Select discipline" />
                    </SelectTrigger>
                    <SelectContent>
                      {mockDisciplines.map(disc => (
                        <SelectItem key={disc} value={disc}>{disc}</SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
              </div>
              <DialogFooter>
                <Button type="button" variant="outline" onClick={() => setIsDialogOpen(false)}>
                  Cancel
                </Button>
                <Button type="submit">
                  <Save className="w-4 h-4 mr-2" />
                  Save Faculty
                </Button>
              </DialogFooter>
            </form>
          </DialogContent>
        </Dialog>
      </div>

      {/* Summary Cards */}
      <div className="grid md:grid-cols-4 gap-4">
        <Card>
          <CardContent className="p-6">
            <div className="flex items-center gap-3">
              <div className="p-3 bg-blue-100 rounded-lg">
                <Users className="w-6 h-6 text-blue-600" />
              </div>
              <div>
                <p className="text-sm text-gray-600">Total Faculty</p>
                <p className="text-2xl">{facultyRecords.length}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-6">
            <div className="flex items-center gap-3">
              <div className="p-3 bg-green-100 rounded-lg">
                <Users className="w-6 h-6 text-green-600" />
              </div>
              <div>
                <p className="text-sm text-gray-600">Full-time</p>
                <p className="text-2xl">
                  {facultyRecords.filter(f => f.employmentType === 'Full-time').length}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-6">
            <div className="flex items-center gap-3">
              <div className="p-3 bg-purple-100 rounded-lg">
                <Users className="w-6 h-6 text-purple-600" />
              </div>
              <div>
                <p className="text-sm text-gray-600">PhD Holders</p>
                <p className="text-2xl">
                  {facultyRecords.filter(f => f.degree === 'PhD').length}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-6">
            <div className="flex items-center gap-3">
              <div className="p-3 bg-orange-100 rounded-lg">
                <Users className="w-6 h-6 text-orange-600" />
              </div>
              <div>
                <p className="text-sm text-gray-600">Part-time</p>
                <p className="text-2xl">
                  {facultyRecords.filter(f => f.employmentType === 'Part-time').length}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Faculty Records ({facultyRecords.length})</CardTitle>
          <CardDescription>Current faculty data entries</CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Name</TableHead>
                <TableHead>Employment Type</TableHead>
                <TableHead>Highest Degree</TableHead>
                <TableHead>Discipline</TableHead>
                <TableHead>Sex</TableHead>
                <TableHead>Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {facultyRecords.map((record) => (
                <TableRow key={record.id}>
                  <TableCell>{record.name}</TableCell>
                  <TableCell>{record.employmentType}</TableCell>
                  <TableCell>{record.degree}</TableCell>
                  <TableCell>{record.discipline}</TableCell>
                  <TableCell>{record.sex}</TableCell>
                  <TableCell>
                    <div className="flex gap-2">
                      <Button variant="ghost" size="sm">
                        <Edit className="w-4 h-4" />
                      </Button>
                      <Button 
                        variant="ghost" 
                        size="sm"
                        onClick={() => handleDelete(record.id)}
                      >
                        <Trash2 className="w-4 h-4 text-red-600" />
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  );
}
