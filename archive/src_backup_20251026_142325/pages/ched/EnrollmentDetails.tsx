import React, { useState } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../components/ui/card';
import { Button } from '../../components/ui/button';
import { Badge } from '../../components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../../components/ui/tabs';
import { ArrowLeft, Download, Calendar, User, BookOpen } from 'lucide-react';

interface StudentRecord {
  id: string;
  studentId: string;
  lastName: string;
  firstName: string;
  middleName: string;
  sex: 'Male' | 'Female';
  program: string;
  yearLevel: string;
  academicYear: string;
  term: string;
  enrollmentStatus: 'Regular' | 'Irregular' | 'Transferee' | 'Returnee';
  createdAt: string;
  updatedAt: string;
  createdBy: string;
  updatedBy: string;
}

interface EnrollmentDetailsProps {
  onNavigate?: (page: string, data?: any) => void;
  heiData?: any;
}

export function EnrollmentDetails({ onNavigate, heiData }: EnrollmentDetailsProps) {

  const [detailFilters, setDetailFilters] = useState({
    academicYear: 'all',
    term: 'all',
    program: 'all',
    sex: 'all',
    yearLevel: 'all'
  });

  // Mock detailed student records
  const generateStudentRecords = (heiName: string): StudentRecord[] => {
    const programs = ['BS Computer Science', 'BS Information Technology', 'BS Business Administration', 'BS Accountancy', 'BS Psychology'];
    const terms = ['1st Semester', '2nd Semester', 'Summer'];
    const academicYears = ['2024-2025', '2023-2024', '2022-2023'];
    const yearLevels = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
    const statuses = ['Regular', 'Irregular', 'Transferee', 'Returnee'] as const;
    
    const records: StudentRecord[] = [];
    const recordCount = 50; // Generate 50 sample records
    
    for (let i = 0; i < recordCount; i++) {
      const sex = Math.random() > 0.5 ? 'Male' : 'Female';
      const createdDate = new Date(2025, 8, Math.floor(Math.random() * 30) + 1);
      const updatedDate = new Date(createdDate.getTime() + Math.random() * 10 * 24 * 60 * 60 * 1000);
      
      records.push({
        id: `enr-${i + 1}`,
        studentId: `2024-${String(i + 1).padStart(5, '0')}`,
        lastName: ['Dela Cruz', 'Santos', 'Reyes', 'Garcia', 'Ramos', 'Mendoza', 'Torres', 'Villanueva'][Math.floor(Math.random() * 8)],
        firstName: sex === 'Male' 
          ? ['Juan', 'Pedro', 'Jose', 'Miguel', 'Carlo', 'Marco'][Math.floor(Math.random() * 6)]
          : ['Maria', 'Ana', 'Liza', 'Rosa', 'Sofia', 'Isabel'][Math.floor(Math.random() * 6)],
        middleName: ['Cruz', 'Santos', 'Rivera', 'Lopez', 'Martinez'][Math.floor(Math.random() * 5)],
        sex,
        program: programs[Math.floor(Math.random() * programs.length)],
        yearLevel: yearLevels[Math.floor(Math.random() * yearLevels.length)],
        academicYear: academicYears[Math.floor(Math.random() * academicYears.length)],
        term: terms[Math.floor(Math.random() * terms.length)],
        enrollmentStatus: statuses[Math.floor(Math.random() * statuses.length)],
        createdAt: createdDate.toISOString(),
        updatedAt: updatedDate.toISOString(),
        createdBy: 'admin@' + heiName.toLowerCase().replace(/\s/g, '') + '.edu.ph',
        updatedBy: 'admin@' + heiName.toLowerCase().replace(/\s/g, '') + '.edu.ph'
      });
    }
    
    return records;
  };

  if (!heiData) {
    return (
      <div className="space-y-6">
        <div className="text-center py-12">
          <p className="text-gray-600 mb-4">No HEI data provided</p>
          <Button onClick={() => onNavigate?.('data-enrollment')}>
            <ArrowLeft className="w-4 h-4 mr-2" />
            Back to Enrollment Data
          </Button>
        </div>
      </div>
    );
  }

  const studentRecords = generateStudentRecords(heiData.heiName);
  
  const getFilteredStudents = (students: StudentRecord[]) => {
    return students.filter(student => {
      const matchesAY = detailFilters.academicYear === 'all' || student.academicYear === detailFilters.academicYear;
      const matchesTerm = detailFilters.term === 'all' || student.term === detailFilters.term;
      const matchesProgram = detailFilters.program === 'all' || student.program === detailFilters.program;
      const matchesSex = detailFilters.sex === 'all' || student.sex === detailFilters.sex;
      const matchesYearLevel = detailFilters.yearLevel === 'all' || student.yearLevel === detailFilters.yearLevel;
      
      return matchesAY && matchesTerm && matchesProgram && matchesSex && matchesYearLevel;
    });
  };

  const filteredStudents = getFilteredStudents(studentRecords);
  
  // Get unique values for filters
  const uniquePrograms = [...new Set(studentRecords.map(s => s.program))];
  const uniqueAY = [...new Set(studentRecords.map(s => s.academicYear))];
  const uniqueTerms = [...new Set(studentRecords.map(s => s.term))];
  const uniqueYearLevels = [...new Set(studentRecords.map(s => s.yearLevel))];

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <Button 
            variant="ghost" 
            onClick={() => onNavigate?.('data-enrollment')}
            className="mb-2 -ml-2"
          >
            <ArrowLeft className="w-4 h-4 mr-2" />
            Back to Enrollment Data
          </Button>
          <h1 className="flex items-center gap-2">
            <BookOpen className="w-8 h-8" style={{ color: 'var(--ph-blue)' }} />
            {heiData.heiName}
          </h1>
          <p className="text-gray-600">Student Enrollment Details</p>
        </div>
      </div>

      {/* Institution Info */}
      <Card>
        <CardContent className="p-6">
          <div className="grid md:grid-cols-4 gap-4">
            <div>
              <p className="text-sm text-gray-600">Region</p>
              <Badge variant="outline" className="mt-1">{heiData.region}</Badge>
            </div>
            <div>
              <p className="text-sm text-gray-600">Total Enrolled</p>
              <p className="text-xl mt-1">{heiData.totalEnrolled.toLocaleString()}</p>
            </div>
            <div>
              <p className="text-sm text-gray-600">Male Students</p>
              <p className="text-xl mt-1">{heiData.male.toLocaleString()}</p>
            </div>
            <div>
              <p className="text-sm text-gray-600">Female Students</p>
              <p className="text-xl mt-1">{heiData.female.toLocaleString()}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Tabs defaultValue="students" className="w-full">
        <TabsList className="grid w-full grid-cols-2">
          <TabsTrigger value="students">Student Records ({filteredStudents.length})</TabsTrigger>
          <TabsTrigger value="summary">Summary Statistics</TabsTrigger>
        </TabsList>

        <TabsContent value="students" className="space-y-4">
          {/* Filters */}
          <Card>
            <CardHeader>
              <CardTitle className="text-base">Filter Records</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                <div className="space-y-2">
                  <label className="text-xs">Academic Year</label>
                  <Select 
                    value={detailFilters.academicYear}
                    onValueChange={(value) => setDetailFilters({...detailFilters, academicYear: value})}
                  >
                    <SelectTrigger className="text-sm">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="all">All Years</SelectItem>
                      {uniqueAY.map(ay => (
                        <SelectItem key={ay} value={ay}>{ay}</SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <div className="space-y-2">
                  <label className="text-xs">Term</label>
                  <Select 
                    value={detailFilters.term}
                    onValueChange={(value) => setDetailFilters({...detailFilters, term: value})}
                  >
                    <SelectTrigger className="text-sm">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="all">All Terms</SelectItem>
                      {uniqueTerms.map(term => (
                        <SelectItem key={term} value={term}>{term}</SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <div className="space-y-2">
                  <label className="text-xs">Program</label>
                  <Select 
                    value={detailFilters.program}
                    onValueChange={(value) => setDetailFilters({...detailFilters, program: value})}
                  >
                    <SelectTrigger className="text-sm">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="all">All Programs</SelectItem>
                      {uniquePrograms.map(program => (
                        <SelectItem key={program} value={program}>{program}</SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <div className="space-y-2">
                  <label className="text-xs">Sex</label>
                  <Select 
                    value={detailFilters.sex}
                    onValueChange={(value) => setDetailFilters({...detailFilters, sex: value})}
                  >
                    <SelectTrigger className="text-sm">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="all">All</SelectItem>
                      <SelectItem value="Male">Male</SelectItem>
                      <SelectItem value="Female">Female</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <div className="space-y-2">
                  <label className="text-xs">Year Level</label>
                  <Select 
                    value={detailFilters.yearLevel}
                    onValueChange={(value) => setDetailFilters({...detailFilters, yearLevel: value})}
                  >
                    <SelectTrigger className="text-sm">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="all">All Levels</SelectItem>
                      {uniqueYearLevels.map(level => (
                        <SelectItem key={level} value={level}>{level}</SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
              </div>

              <div className="flex gap-2 mt-3">
                <Button 
                  variant="outline" 
                  size="sm"
                  onClick={() => setDetailFilters({
                    academicYear: 'all',
                    term: 'all',
                    program: 'all',
                    sex: 'all',
                    yearLevel: 'all'
                  })}
                >
                  Clear Filters
                </Button>
                <Button size="sm" variant="outline">
                  <Download className="w-4 h-4 mr-2" />
                  Export Filtered ({filteredStudents.length})
                </Button>
              </div>
            </CardContent>
          </Card>

          {/* Student Records Table */}
          <Card>
            <CardContent className="p-0">
              <div className="overflow-x-auto">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Student ID</TableHead>
                      <TableHead>Name</TableHead>
                      <TableHead>Sex</TableHead>
                      <TableHead>Program</TableHead>
                      <TableHead>Year Level</TableHead>
                      <TableHead>AY</TableHead>
                      <TableHead>Term</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead>Created</TableHead>
                      <TableHead>Updated</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {filteredStudents.map((student) => (
                      <TableRow key={student.id}>
                        <TableCell className="font-mono text-xs">{student.studentId}</TableCell>
                        <TableCell>
                          <div className="min-w-[180px]">
                            <p className="text-sm">{student.lastName}, {student.firstName}</p>
                            <p className="text-xs text-gray-500">{student.middleName}</p>
                          </div>
                        </TableCell>
                        <TableCell>
                          <Badge variant="outline" className="text-xs">
                            {student.sex}
                          </Badge>
                        </TableCell>
                        <TableCell className="min-w-[200px] text-xs">{student.program}</TableCell>
                        <TableCell className="text-xs">{student.yearLevel}</TableCell>
                        <TableCell className="text-xs">{student.academicYear}</TableCell>
                        <TableCell className="text-xs">{student.term}</TableCell>
                        <TableCell>
                          <Badge 
                            variant={student.enrollmentStatus === 'Regular' ? 'default' : 'secondary'}
                            className="text-xs"
                          >
                            {student.enrollmentStatus}
                          </Badge>
                        </TableCell>
                        <TableCell className="text-xs">
                          <div className="min-w-[140px]">
                            <p>{new Date(student.createdAt).toLocaleDateString()}</p>
                            <p className="text-gray-500 text-xs">{new Date(student.createdAt).toLocaleTimeString()}</p>
                          </div>
                        </TableCell>
                        <TableCell className="text-xs">
                          <div className="min-w-[140px]">
                            <p>{new Date(student.updatedAt).toLocaleDateString()}</p>
                            <p className="text-gray-500 text-xs">{new Date(student.updatedAt).toLocaleTimeString()}</p>
                          </div>
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="summary" className="space-y-4">
          <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
            <Card>
              <CardHeader>
                <CardTitle className="text-sm text-gray-600">Total Students</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-3xl">{filteredStudents.length}</p>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle className="text-sm text-gray-600">Male Students</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-3xl">{filteredStudents.filter(s => s.sex === 'Male').length}</p>
                <p className="text-xs text-gray-500">
                  {filteredStudents.length > 0 ? ((filteredStudents.filter(s => s.sex === 'Male').length / filteredStudents.length) * 100).toFixed(1) : '0'}%
                </p>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle className="text-sm text-gray-600">Female Students</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-3xl">{filteredStudents.filter(s => s.sex === 'Female').length}</p>
                <p className="text-xs text-gray-500">
                  {filteredStudents.length > 0 ? ((filteredStudents.filter(s => s.sex === 'Female').length / filteredStudents.length) * 100).toFixed(1) : '0'}%
                </p>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle className="text-sm text-gray-600">Regular Students</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-3xl">{filteredStudents.filter(s => s.enrollmentStatus === 'Regular').length}</p>
                <p className="text-xs text-gray-500">
                  {filteredStudents.length > 0 ? ((filteredStudents.filter(s => s.enrollmentStatus === 'Regular').length / filteredStudents.length) * 100).toFixed(1) : '0'}%
                </p>
              </CardContent>
            </Card>
          </div>

          <Card>
            <CardHeader>
              <CardTitle className="text-base">Program Distribution</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-3">
                {uniquePrograms.map(program => {
                  const count = filteredStudents.filter(s => s.program === program).length;
                  const percentage = filteredStudents.length > 0 ? (count / filteredStudents.length) * 100 : 0;
                  return (
                    <div key={program} className="space-y-1">
                      <div className="flex justify-between text-sm">
                        <span>{program}</span>
                        <span>{count} ({percentage.toFixed(1)}%)</span>
                      </div>
                      <div className="w-full bg-gray-200 rounded-full h-2">
                        <div 
                          className="h-2 rounded-full" 
                          style={{ 
                            width: `${percentage}%`,
                            backgroundColor: 'var(--ph-blue)'
                          }}
                        />
                      </div>
                    </div>
                  );
                })}
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle className="text-base">Audit Information</CardTitle>
            </CardHeader>
            <CardContent className="space-y-2 text-sm">
              {filteredStudents.length > 0 && (
                <>
                  <div className="flex items-center gap-2">
                    <Calendar className="w-4 h-4 text-gray-500" />
                    <span className="text-gray-600">Last Updated:</span>
                    <span>{new Date(Math.max(...filteredStudents.map(s => new Date(s.updatedAt).getTime()))).toLocaleString()}</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <User className="w-4 h-4 text-gray-500" />
                    <span className="text-gray-600">Last Updated By:</span>
                    <span>{filteredStudents[0]?.updatedBy || 'N/A'}</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <Calendar className="w-4 h-4 text-gray-500" />
                    <span className="text-gray-600">First Record Created:</span>
                    <span>{new Date(Math.min(...filteredStudents.map(s => new Date(s.createdAt).getTime()))).toLocaleString()}</span>
                  </div>
                </>
              )}
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  );
}
