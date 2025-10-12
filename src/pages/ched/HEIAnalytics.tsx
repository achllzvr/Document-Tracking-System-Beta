import React, { useState } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../../components/ui/tabs';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../components/ui/select';
import { Button } from '../../components/ui/button';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, PieChart, Pie, Cell, LineChart, Line } from 'recharts';
import { Download, Filter } from 'lucide-react';
import { mockRegions, mockHEIs } from '../../data/mockData';

export function HEIAnalytics() {
  const [regionFilter, setRegionFilter] = useState('all');
  const [domain, setDomain] = useState('institutional');

  // Mock data for charts
  const enrollmentByProgram = [
    { program: 'Computer Science', male: 450, female: 320 },
    { program: 'Engineering', male: 380, female: 210 },
    { program: 'Business', male: 290, female: 340 },
    { program: 'Education', male: 180, female: 420 },
    { program: 'Nursing', male: 120, female: 380 },
  ];

  const enrollmentTrend = [
    { year: '2020-21', total: 45000 },
    { year: '2021-22', total: 48500 },
    { year: '2022-23', total: 52000 },
    { year: '2023-24', total: 54500 },
    { year: '2024-25', total: 57000 },
  ];

  const heisByType = [
    { name: 'State University', value: 115, color: '#3b82f6' },
    { name: 'State College', value: 245, color: '#8b5cf6' },
    { name: 'Private University', value: 385, color: '#10b981' },
    { name: 'Private College', value: 620, color: '#f59e0b' },
    { name: 'Local University', value: 85, color: '#ef4444' },
    { name: 'Local College', value: 150, color: '#ec4899' },
  ];

  const graduatesByDiscipline = [
    { discipline: 'Engineering', count: 4500 },
    { discipline: 'Business', count: 3800 },
    { discipline: 'Education', count: 3200 },
    { discipline: 'IT', count: 2900 },
    { discipline: 'Medicine', count: 1800 },
  ];

  const COLORS = ['#3b82f6', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444'];

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1>HEI Data Analytics</h1>
          <p className="text-gray-600">Explore and analyze higher education data with interactive visualizations</p>
        </div>
        <Button>
          <Download className="w-4 h-4 mr-2" />
          Export Report
        </Button>
      </div>

      {/* Filters */}
      <Card>
        <CardHeader>
          <CardTitle>Filters</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid md:grid-cols-4 gap-4">
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

            <Select>
              <SelectTrigger>
                <SelectValue placeholder="All HEIs" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All HEIs</SelectItem>
                {mockHEIs.slice(0, 5).map(hei => (
                  <SelectItem key={hei.id} value={hei.id}>{hei.shortName}</SelectItem>
                ))}
              </SelectContent>
            </Select>

            <Select>
              <SelectTrigger>
                <SelectValue placeholder="Academic Year" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="2024-2025">2024-2025</SelectItem>
                <SelectItem value="2023-2024">2023-2024</SelectItem>
              </SelectContent>
            </Select>

            <Button variant="outline">
              <Filter className="w-4 h-4 mr-2" />
              Apply Filters
            </Button>
          </div>
        </CardContent>
      </Card>

      {/* Tabs for different domains */}
      <Tabs value={domain} onValueChange={setDomain}>
        <TabsList className="grid w-full grid-cols-4">
          <TabsTrigger value="institutional">Institutional</TabsTrigger>
          <TabsTrigger value="enrollment">Enrollment</TabsTrigger>
          <TabsTrigger value="graduates">Graduates</TabsTrigger>
          <TabsTrigger value="faculty">Faculty</TabsTrigger>
        </TabsList>

        <TabsContent value="institutional" className="space-y-6">
          <div className="grid lg:grid-cols-2 gap-6">
            <Card>
              <CardHeader>
                <CardTitle>HEIs by Institution Type</CardTitle>
                <CardDescription>Distribution of higher education institutions</CardDescription>
              </CardHeader>
              <CardContent>
                <ResponsiveContainer width="100%" height={300}>
                  <PieChart>
                    <Pie
                      data={heisByType}
                      cx="50%"
                      cy="50%"
                      labelLine={false}
                      label={(entry) => entry.name}
                      outerRadius={80}
                      fill="#8884d8"
                      dataKey="value"
                    >
                      {heisByType.map((entry, index) => (
                        <Cell key={`cell-${index}`} fill={entry.color} />
                      ))}
                    </Pie>
                    <Tooltip />
                  </PieChart>
                </ResponsiveContainer>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Regional Distribution</CardTitle>
                <CardDescription>Number of HEIs per region</CardDescription>
              </CardHeader>
              <CardContent>
                <ResponsiveContainer width="100%" height={300}>
                  <BarChart data={[
                    { region: 'NCR', count: 280 },
                    { region: 'Region III', count: 145 },
                    { region: 'Region IV-A', count: 198 },
                    { region: 'Region VII', count: 165 },
                    { region: 'Region XI', count: 132 }
                  ]}>
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis dataKey="region" />
                    <YAxis />
                    <Tooltip />
                    <Bar dataKey="count" fill="#3b82f6" />
                  </BarChart>
                </ResponsiveContainer>
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        <TabsContent value="enrollment" className="space-y-6">
          <div className="grid lg:grid-cols-2 gap-6">
            <Card>
              <CardHeader>
                <CardTitle>Enrollment by Program and Gender</CardTitle>
                <CardDescription>Student distribution across programs</CardDescription>
              </CardHeader>
              <CardContent>
                <ResponsiveContainer width="100%" height={300}>
                  <BarChart data={enrollmentByProgram}>
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis dataKey="program" />
                    <YAxis />
                    <Tooltip />
                    <Legend />
                    <Bar dataKey="male" fill="#3b82f6" name="Male" />
                    <Bar dataKey="female" fill="#ec4899" name="Female" />
                  </BarChart>
                </ResponsiveContainer>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Enrollment Trend</CardTitle>
                <CardDescription>Total enrollment over the years</CardDescription>
              </CardHeader>
              <CardContent>
                <ResponsiveContainer width="100%" height={300}>
                  <LineChart data={enrollmentTrend}>
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis dataKey="year" />
                    <YAxis />
                    <Tooltip />
                    <Line type="monotone" dataKey="total" stroke="#3b82f6" strokeWidth={2} />
                  </LineChart>
                </ResponsiveContainer>
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        <TabsContent value="graduates" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Graduates by Discipline</CardTitle>
              <CardDescription>Number of graduates per discipline for AY 2023-2024</CardDescription>
            </CardHeader>
            <CardContent>
              <ResponsiveContainer width="100%" height={350}>
                <BarChart data={graduatesByDiscipline} layout="horizontal">
                  <CartesianGrid strokeDasharray="3 3" />
                  <XAxis type="number" />
                  <YAxis dataKey="discipline" type="category" width={100} />
                  <Tooltip />
                  <Bar dataKey="count" fill="#10b981" />
                </BarChart>
              </ResponsiveContainer>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="faculty" className="space-y-6">
          <div className="grid lg:grid-cols-2 gap-6">
            <Card>
              <CardHeader>
                <CardTitle>Faculty by Employment Type</CardTitle>
                <CardDescription>Distribution of faculty employment</CardDescription>
              </CardHeader>
              <CardContent>
                <ResponsiveContainer width="100%" height={300}>
                  <PieChart>
                    <Pie
                      data={[
                        { name: 'Full-time', value: 12500 },
                        { name: 'Part-time', value: 8200 },
                        { name: 'Contractual', value: 4800 },
                        { name: 'Visiting', value: 1500 }
                      ]}
                      cx="50%"
                      cy="50%"
                      labelLine={false}
                      label={(entry) => `${entry.name}: ${entry.value}`}
                      outerRadius={80}
                      fill="#8884d8"
                      dataKey="value"
                    >
                      {COLORS.map((color, index) => (
                        <Cell key={`cell-${index}`} fill={color} />
                      ))}
                    </Pie>
                    <Tooltip />
                  </PieChart>
                </ResponsiveContainer>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Faculty by Highest Degree</CardTitle>
                <CardDescription>Educational qualifications of faculty</CardDescription>
              </CardHeader>
              <CardContent>
                <ResponsiveContainer width="100%" height={300}>
                  <BarChart data={[
                    { degree: 'PhD', count: 8500 },
                    { degree: 'Masters', count: 14200 },
                    { degree: 'Bachelors', count: 4300 }
                  ]}>
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis dataKey="degree" />
                    <YAxis />
                    <Tooltip />
                    <Bar dataKey="count" fill="#8b5cf6" />
                  </BarChart>
                </ResponsiveContainer>
              </CardContent>
            </Card>
          </div>
        </TabsContent>
      </Tabs>
    </div>
  );
}
