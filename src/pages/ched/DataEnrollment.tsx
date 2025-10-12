import React, { useState } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../components/ui/card';
import { Input } from '../../components/ui/input';
import { Button } from '../../components/ui/button';
import { Badge } from '../../components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../components/ui/table';
import { Search, Download, Eye, User, BookOpen } from 'lucide-react';
import { mockHEIs, mockRegions } from '../../data/mockData';

interface DataEnrollmentProps {
  onNavigate?: (page: string, data?: any) => void;
}

export function DataEnrollment({ onNavigate }: DataEnrollmentProps) {
  const [searchTerm, setSearchTerm] = useState('');
  const [heiFilter, setHeiFilter] = useState('all');
  const [regionFilter, setRegionFilter] = useState('all');

  // Mock enrollment summary data
  const enrollmentData = mockHEIs.map(hei => ({
    heiId: hei.id,
    heiName: hei.name,
    region: hei.region,
    totalEnrolled: Math.floor(Math.random() * 10000) + 1000,
    male: Math.floor(Math.random() * 5000) + 500,
    female: Math.floor(Math.random() * 5000) + 500,
    lastUpdated: '2025-10-10',
  }));

  const filteredData = enrollmentData.filter(item => {
    const matchesSearch = item.heiName.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesHEI = heiFilter === 'all' || item.heiId === heiFilter;
    const matchesRegion = regionFilter === 'all' || item.region === regionFilter;
    return matchesSearch && matchesHEI && matchesRegion;
  });

  const handleViewDetails = (hei: any) => {
    if (onNavigate) {
      onNavigate('enrollment-details', hei);
    }
  };

  return (
    <div className="space-y-6">
      <div>
        <h1>Enrollment Data</h1>
        <p className="text-gray-600">View and export enrollment statistics across all HEIs</p>
      </div>

      <div className="grid md:grid-cols-4 gap-4">
        <Card>
          <CardContent className="p-6">
            <div className="flex items-center gap-3">
              <div className="p-3 rounded-lg" style={{ backgroundColor: 'var(--ph-blue-lighter)' }}>
                <User className="w-6 h-6" style={{ color: 'var(--ph-blue)' }} />
              </div>
              <div>
                <p className="text-sm text-gray-600">Total Students</p>
                <p className="text-2xl">
                  {filteredData.reduce((sum, item) => sum + item.totalEnrolled, 0).toLocaleString()}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-6">
            <div className="flex items-center gap-3">
              <div className="p-3 rounded-lg" style={{ backgroundColor: 'var(--ph-blue-lighter)' }}>
                <User className="w-6 h-6" style={{ color: 'var(--ph-blue)' }} />
              </div>
              <div>
                <p className="text-sm text-gray-600">Male Students</p>
                <p className="text-2xl">
                  {filteredData.reduce((sum, item) => sum + item.male, 0).toLocaleString()}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-6">
            <div className="flex items-center gap-3">
              <div className="p-3 rounded-lg" style={{ backgroundColor: 'var(--ph-red-lighter)' }}>
                <User className="w-6 h-6" style={{ color: 'var(--ph-red)' }} />
              </div>
              <div>
                <p className="text-sm text-gray-600">Female Students</p>
                <p className="text-2xl">
                  {filteredData.reduce((sum, item) => sum + item.female, 0).toLocaleString()}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-6">
            <div className="flex items-center gap-3">
              <div className="p-3 rounded-lg" style={{ backgroundColor: 'var(--ph-yellow-lighter)' }}>
                <BookOpen className="w-6 h-6" style={{ color: '#d4a017' }} />
              </div>
              <div>
                <p className="text-sm text-gray-600">HEIs Reporting</p>
                <p className="text-2xl">{filteredData.length}</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Search & Filter</CardTitle>
          <CardDescription>Find enrollment data by institution or region</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="flex flex-col md:flex-row gap-4">
            <div className="flex-1 relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
              <Input
                placeholder="Search by institution name..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="pl-10"
              />
            </div>

            <Select value={regionFilter} onValueChange={setRegionFilter}>
              <SelectTrigger className="w-full md:w-[200px]">
                <SelectValue placeholder="Filter by Region" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Regions</SelectItem>
                {mockRegions.map(region => (
                  <SelectItem key={region} value={region}>{region}</SelectItem>
                ))}
              </SelectContent>
            </Select>

            <Button>
              <Download className="w-4 h-4 mr-2" />
              Export All
            </Button>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Enrollment Summary ({filteredData.length} institutions)</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Institution</TableHead>
                <TableHead>Region</TableHead>
                <TableHead>Total Enrolled</TableHead>
                <TableHead>Male</TableHead>
                <TableHead>Female</TableHead>
                <TableHead>Last Updated</TableHead>
                <TableHead>Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {filteredData.map((item) => (
                <TableRow key={item.heiId}>
                  <TableCell className="max-w-xs">
                    <p>{item.heiName}</p>
                  </TableCell>
                  <TableCell>
                    <Badge variant="outline">{item.region}</Badge>
                  </TableCell>
                  <TableCell>{item.totalEnrolled.toLocaleString()}</TableCell>
                  <TableCell>{item.male.toLocaleString()}</TableCell>
                  <TableCell>{item.female.toLocaleString()}</TableCell>
                  <TableCell>{new Date(item.lastUpdated).toLocaleDateString()}</TableCell>
                  <TableCell>
                    <Button 
                      variant="ghost" 
                      size="sm"
                      onClick={() => handleViewDetails(item)}
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
