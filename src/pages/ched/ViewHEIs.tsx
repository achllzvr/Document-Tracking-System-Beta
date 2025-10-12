import React, { useState } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../components/ui/card';
import { Input } from '../../components/ui/input';
import { Button } from '../../components/ui/button';
import { Badge } from '../../components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../components/ui/table';
import { Search, Eye } from 'lucide-react';
import { mockHEIs, mockRegions, mockInstitutionTypes, mockOwnershipForms } from '../../data/mockData';

interface ViewHEIsProps {
  onNavigate?: (page: string, data?: any) => void;
}

export function ViewHEIs({ onNavigate }: ViewHEIsProps) {
  const [searchTerm, setSearchTerm] = useState('');
  const [regionFilter, setRegionFilter] = useState('all');
  const [typeFilter, setTypeFilter] = useState('all');
  const [ownershipFilter, setOwnershipFilter] = useState('all');

  const filteredHEIs = mockHEIs.filter(hei => {
    const matchesSearch = hei.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         hei.shortName.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesRegion = regionFilter === 'all' || hei.region === regionFilter;
    const matchesType = typeFilter === 'all' || hei.institutionType === typeFilter;
    const matchesOwnership = ownershipFilter === 'all' || hei.ownershipForm === ownershipFilter;
    
    return matchesSearch && matchesRegion && matchesType && matchesOwnership;
  });

  return (
    <div className="space-y-6">
      <div>
        <h1>Higher Education Institutions</h1>
        <p className="text-gray-600">Browse and manage institutional profiles</p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Search & Filter</CardTitle>
          <CardDescription>Find institutions by name, region, type, or ownership</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid md:grid-cols-4 gap-4">
            <div className="relative">
              <Search className="absolute left-3 top-3 w-4 h-4 text-gray-400" />
              <Input
                placeholder="Search by name..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="pl-9"
              />
            </div>
            
            <Select value={regionFilter} onValueChange={setRegionFilter}>
              <SelectTrigger>
                <SelectValue placeholder="All Regions" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Regions</SelectItem>
                {mockRegions.map(region => (
                  <SelectItem key={region} value={region}>{region}</SelectItem>
                ))}
              </SelectContent>
            </Select>

            <Select value={typeFilter} onValueChange={setTypeFilter}>
              <SelectTrigger>
                <SelectValue placeholder="All Types" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Types</SelectItem>
                {mockInstitutionTypes.map(type => (
                  <SelectItem key={type} value={type}>{type}</SelectItem>
                ))}
              </SelectContent>
            </Select>

            <Select value={ownershipFilter} onValueChange={setOwnershipFilter}>
              <SelectTrigger>
                <SelectValue placeholder="All Ownership" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Ownership</SelectItem>
                {mockOwnershipForms.map(form => (
                  <SelectItem key={form} value={form}>{form}</SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Institutions ({filteredHEIs.length})</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Name</TableHead>
                <TableHead>Region</TableHead>
                <TableHead>Type</TableHead>
                <TableHead>Ownership</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {filteredHEIs.map((hei) => (
                <TableRow key={hei.id}>
                  <TableCell>
                    <div>
                      <p>{hei.name}</p>
                      <p className="text-sm text-gray-500">{hei.shortName}</p>
                    </div>
                  </TableCell>
                  <TableCell>{hei.region}</TableCell>
                  <TableCell>{hei.institutionType}</TableCell>
                  <TableCell>{hei.ownershipForm}</TableCell>
                  <TableCell>
                    <Badge variant={hei.status === 'Active' ? 'default' : 'secondary'}>
                      {hei.status}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <Button 
                      variant="ghost" 
                      size="sm"
                      onClick={() => onNavigate?.('hei-details', { heiId: hei.id })}
                    >
                      <Eye className="w-4 h-4 mr-2" />
                      View Details
                    </Button>
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
