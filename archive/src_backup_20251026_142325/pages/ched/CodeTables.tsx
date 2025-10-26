import React, { useState } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../components/ui/card';
import { Button } from '../../components/ui/button';
import { Input } from '../../components/ui/input';
import { Label } from '../../components/ui/label';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../../components/ui/tabs';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../components/ui/table';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '../../components/ui/dialog';
import { Plus, Edit, Trash2, Save } from 'lucide-react';
import { mockRegions, mockInstitutionTypes, mockOwnershipForms, mockEmploymentTypes, mockDegrees, mockDisciplines } from '../../data/mockData';
import { toast } from 'sonner@2.0.3';

export function CodeTables() {
  const [isDialogOpen, setIsDialogOpen] = useState(false);
  const [editingItem, setEditingItem] = useState<{ code: string; description: string } | null>(null);

  // Mock code table data
  const [degreeCodes, setDegreeCodes] = useState([
    { code: 'PHD', description: 'Doctor of Philosophy', active: true },
    { code: 'MS', description: 'Master of Science', active: true },
    { code: 'MA', description: 'Master of Arts', active: true },
    { code: 'BS', description: 'Bachelor of Science', active: true },
    { code: 'BA', description: 'Bachelor of Arts', active: true },
  ]);

  const [disciplineCodes, setDisciplineCodes] = useState([
    { code: 'CS', description: 'Computer Science', active: true },
    { code: 'ENG', description: 'Engineering', active: true },
    { code: 'BUS', description: 'Business Administration', active: true },
    { code: 'EDU', description: 'Education', active: true },
    { code: 'MED', description: 'Medicine', active: true },
  ]);

  const handleAddItem = (code: string, description: string) => {
    toast.success('Code added successfully');
    setIsDialogOpen(false);
  };

  const handleDeleteItem = (code: string) => {
    toast.success('Code deleted successfully');
  };

  const CodeTableSection = ({ 
    title, 
    description, 
    data, 
    onAdd, 
    onDelete 
  }: { 
    title: string; 
    description: string; 
    data: Array<{ code: string; description: string; active: boolean }>;
    onAdd: (code: string, desc: string) => void;
    onDelete: (code: string) => void;
  }) => (
    <Card>
      <CardHeader>
        <div className="flex items-center justify-between">
          <div>
            <CardTitle>{title}</CardTitle>
            <CardDescription>{description}</CardDescription>
          </div>
          <Dialog open={isDialogOpen} onOpenChange={setIsDialogOpen}>
            <DialogTrigger asChild>
              <Button>
                <Plus className="w-4 h-4 mr-2" />
                Add Code
              </Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Add New Code</DialogTitle>
                <DialogDescription>Add a new code to {title.toLowerCase()}</DialogDescription>
              </DialogHeader>
              <div className="space-y-4 py-4">
                <div className="space-y-2">
                  <Label htmlFor="code">Code</Label>
                  <Input id="code" placeholder="e.g., CS" />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="description">Description</Label>
                  <Input id="description" placeholder="e.g., Computer Science" />
                </div>
              </div>
              <DialogFooter>
                <Button variant="outline" onClick={() => setIsDialogOpen(false)}>
                  Cancel
                </Button>
                <Button onClick={() => handleAddItem('CS', 'Computer Science')}>
                  <Save className="w-4 h-4 mr-2" />
                  Add Code
                </Button>
              </DialogFooter>
            </DialogContent>
          </Dialog>
        </div>
      </CardHeader>
      <CardContent>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Code</TableHead>
              <TableHead>Description</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {data.map((item) => (
              <TableRow key={item.code}>
                <TableCell className="font-mono">{item.code}</TableCell>
                <TableCell>{item.description}</TableCell>
                <TableCell>
                  <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs ${
                    item.active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'
                  }`}>
                    {item.active ? 'Active' : 'Inactive'}
                  </span>
                </TableCell>
                <TableCell>
                  <div className="flex gap-2">
                    <Button variant="ghost" size="sm">
                      <Edit className="w-4 h-4" />
                    </Button>
                    <Button 
                      variant="ghost" 
                      size="sm"
                      onClick={() => handleDeleteItem(item.code)}
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
  );

  return (
    <div className="space-y-6">
      <div>
        <h1>Code Tables Management</h1>
        <p className="text-gray-600">Manage reference tables and lookup codes used throughout the system</p>
      </div>

      <Tabs defaultValue="degrees" className="space-y-6">
        <TabsList>
          <TabsTrigger value="degrees">Degree Codes</TabsTrigger>
          <TabsTrigger value="disciplines">Discipline Codes</TabsTrigger>
          <TabsTrigger value="employment">Employment Types</TabsTrigger>
          <TabsTrigger value="institutions">Institution Types</TabsTrigger>
          <TabsTrigger value="ownership">Ownership Forms</TabsTrigger>
          <TabsTrigger value="regions">Regions</TabsTrigger>
        </TabsList>

        <TabsContent value="degrees">
          <CodeTableSection
            title="Degree Codes"
            description="Academic degree classifications"
            data={degreeCodes}
            onAdd={handleAddItem}
            onDelete={handleDeleteItem}
          />
        </TabsContent>

        <TabsContent value="disciplines">
          <CodeTableSection
            title="Discipline Codes"
            description="Academic and professional disciplines"
            data={disciplineCodes}
            onAdd={handleAddItem}
            onDelete={handleDeleteItem}
          />
        </TabsContent>

        <TabsContent value="employment">
          <Card>
            <CardHeader>
              <CardTitle>Employment Types</CardTitle>
              <CardDescription>Faculty employment classifications</CardDescription>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Type</TableHead>
                    <TableHead>Description</TableHead>
                    <TableHead>Count</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {mockEmploymentTypes.map((type, index) => (
                    <TableRow key={index}>
                      <TableCell>{type}</TableCell>
                      <TableCell>Faculty employed as {type.toLowerCase()}</TableCell>
                      <TableCell className="text-gray-500">
                        {Math.floor(Math.random() * 5000)} records
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="institutions">
          <Card>
            <CardHeader>
              <CardTitle>Institution Types</CardTitle>
              <CardDescription>Higher education institution classifications</CardDescription>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Type</TableHead>
                    <TableHead>HEI Count</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {mockInstitutionTypes.map((type, index) => (
                    <TableRow key={index}>
                      <TableCell>{type}</TableCell>
                      <TableCell className="text-gray-500">
                        {Math.floor(Math.random() * 300)} institutions
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="ownership">
          <Card>
            <CardHeader>
              <CardTitle>Ownership Forms</CardTitle>
              <CardDescription>Institution ownership classifications</CardDescription>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Form</TableHead>
                    <TableHead>HEI Count</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {mockOwnershipForms.map((form, index) => (
                    <TableRow key={index}>
                      <TableCell>{form}</TableCell>
                      <TableCell className="text-gray-500">
                        {Math.floor(Math.random() * 400)} institutions
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="regions">
          <Card>
            <CardHeader>
              <CardTitle>National Regions</CardTitle>
              <CardDescription>Philippine regional classifications</CardDescription>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Region</TableHead>
                    <TableHead>HEI Count</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {mockRegions.map((region, index) => (
                    <TableRow key={index}>
                      <TableCell>{region}</TableCell>
                      <TableCell className="text-gray-500">
                        {Math.floor(Math.random() * 200)} institutions
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
