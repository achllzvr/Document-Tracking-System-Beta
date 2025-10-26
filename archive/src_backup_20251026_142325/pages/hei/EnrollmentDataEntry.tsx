import React, { useState } from 'react';
import { useAuth } from '../../context/AuthContext';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../components/ui/card';
import { Button } from '../../components/ui/button';
import { Input } from '../../components/ui/input';
import { Label } from '../../components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../components/ui/table';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '../../components/ui/dialog';
import { Plus, Edit, Trash2, Save } from 'lucide-react';
import { toast } from 'sonner@2.0.3';

export function EnrollmentDataEntry() {
  const { user } = useAuth();
  const [isDialogOpen, setIsDialogOpen] = useState(false);
  const [formData, setFormData] = useState({
    academicYear: '2024-2025',
    term: '1st Semester',
    program: '',
    sex: 'Male',
    yearLevel: '1st Year',
    count: ''
  });

  // Mock enrollment data
  const [enrollmentRecords, setEnrollmentRecords] = useState([
    { id: 1, academicYear: '2024-2025', term: '1st Semester', program: 'BS Computer Science', sex: 'Male', yearLevel: '1st Year', count: 145 },
    { id: 2, academicYear: '2024-2025', term: '1st Semester', program: 'BS Computer Science', sex: 'Female', yearLevel: '1st Year', count: 98 },
    { id: 3, academicYear: '2024-2025', term: '1st Semester', program: 'BS Information Technology', sex: 'Male', yearLevel: '2nd Year', count: 87 },
  ]);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const newRecord = {
      id: enrollmentRecords.length + 1,
      ...formData,
      count: parseInt(formData.count)
    };
    setEnrollmentRecords([...enrollmentRecords, newRecord]);
    setIsDialogOpen(false);
    setFormData({
      academicYear: '2024-2025',
      term: '1st Semester',
      program: '',
      sex: 'Male',
      yearLevel: '1st Year',
      count: ''
    });
    toast.success('Enrollment record added successfully');
  };

  const handleDelete = (id: number) => {
    setEnrollmentRecords(enrollmentRecords.filter(r => r.id !== id));
    toast.success('Record deleted successfully');
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1>Enrollment Data Entry</h1>
          <p className="text-gray-600">Manage enrollment records for {user?.heiName}</p>
        </div>
        
        <Dialog open={isDialogOpen} onOpenChange={setIsDialogOpen}>
          <DialogTrigger asChild>
            <Button>
              <Plus className="w-4 h-4 mr-2" />
              Add Record
            </Button>
          </DialogTrigger>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Add Enrollment Record</DialogTitle>
              <DialogDescription>
                Enter enrollment data for a specific program and demographic
              </DialogDescription>
            </DialogHeader>
            <form onSubmit={handleSubmit}>
              <div className="space-y-4 py-4">
                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="academicYear">Academic Year</Label>
                    <Select 
                      value={formData.academicYear}
                      onValueChange={(value) => setFormData({...formData, academicYear: value})}
                    >
                      <SelectTrigger id="academicYear">
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="2024-2025">2024-2025</SelectItem>
                        <SelectItem value="2023-2024">2023-2024</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                  
                  <div className="space-y-2">
                    <Label htmlFor="term">Term</Label>
                    <Select 
                      value={formData.term}
                      onValueChange={(value) => setFormData({...formData, term: value})}
                    >
                      <SelectTrigger id="term">
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="1st Semester">1st Semester</SelectItem>
                        <SelectItem value="2nd Semester">2nd Semester</SelectItem>
                        <SelectItem value="Summer">Summer</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="program">Program</Label>
                  <Input
                    id="program"
                    value={formData.program}
                    onChange={(e) => setFormData({...formData, program: e.target.value})}
                    placeholder="e.g., BS Computer Science"
                    required
                  />
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="sex">Sex</Label>
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

                  <div className="space-y-2">
                    <Label htmlFor="yearLevel">Year Level</Label>
                    <Select 
                      value={formData.yearLevel}
                      onValueChange={(value) => setFormData({...formData, yearLevel: value})}
                    >
                      <SelectTrigger id="yearLevel">
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="1st Year">1st Year</SelectItem>
                        <SelectItem value="2nd Year">2nd Year</SelectItem>
                        <SelectItem value="3rd Year">3rd Year</SelectItem>
                        <SelectItem value="4th Year">4th Year</SelectItem>
                        <SelectItem value="5th Year">5th Year</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="count">Student Count</Label>
                  <Input
                    id="count"
                    type="number"
                    value={formData.count}
                    onChange={(e) => setFormData({...formData, count: e.target.value})}
                    placeholder="0"
                    min="0"
                    required
                  />
                </div>
              </div>
              <DialogFooter>
                <Button type="button" variant="outline" onClick={() => setIsDialogOpen(false)}>
                  Cancel
                </Button>
                <Button type="submit">
                  <Save className="w-4 h-4 mr-2" />
                  Save Record
                </Button>
              </DialogFooter>
            </form>
          </DialogContent>
        </Dialog>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Enrollment Records ({enrollmentRecords.length})</CardTitle>
          <CardDescription>Current enrollment data entries</CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Academic Year</TableHead>
                <TableHead>Term</TableHead>
                <TableHead>Program</TableHead>
                <TableHead>Sex</TableHead>
                <TableHead>Year Level</TableHead>
                <TableHead className="text-right">Count</TableHead>
                <TableHead>Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {enrollmentRecords.map((record) => (
                <TableRow key={record.id}>
                  <TableCell>{record.academicYear}</TableCell>
                  <TableCell>{record.term}</TableCell>
                  <TableCell>{record.program}</TableCell>
                  <TableCell>{record.sex}</TableCell>
                  <TableCell>{record.yearLevel}</TableCell>
                  <TableCell className="text-right">{record.count}</TableCell>
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
