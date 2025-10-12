import React from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../components/ui/card';
import { Badge } from '../../components/ui/badge';
import { Button } from '../../components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../components/ui/table';
import { CheckCircle, AlertCircle, XCircle, Eye, RefreshCw } from 'lucide-react';
import { mockETLJobs } from '../../data/mockData';

export function ETLJobs() {
  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'Success':
        return <CheckCircle className="w-5 h-5 text-green-500" />;
      case 'Failed':
        return <XCircle className="w-5 h-5 text-red-500" />;
      case 'Partial':
        return <AlertCircle className="w-5 h-5 text-yellow-500" />;
      default:
        return null;
    }
  };

  return (
    <div className="space-y-6">
      <div>
        <h1>ETL Jobs</h1>
        <p className="text-gray-600">Monitor data ingestion and processing jobs from HEI uploads</p>
      </div>

      <div className="grid md:grid-cols-3 gap-4">
        <Card>
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-gray-600">Total Jobs</p>
                <p className="text-2xl mt-1">{mockETLJobs.length}</p>
              </div>
              <div className="p-3 bg-blue-100 rounded-lg">
                <RefreshCw className="w-6 h-6 text-blue-600" />
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-gray-600">Successful</p>
                <p className="text-2xl mt-1">
                  {mockETLJobs.filter(j => j.status === 'Success').length}
                </p>
              </div>
              <div className="p-3 bg-green-100 rounded-lg">
                <CheckCircle className="w-6 h-6 text-green-600" />
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-gray-600">Failed</p>
                <p className="text-2xl mt-1">
                  {mockETLJobs.filter(j => j.status === 'Failed').length}
                </p>
              </div>
              <div className="p-3 bg-red-100 rounded-lg">
                <XCircle className="w-6 h-6 text-red-600" />
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Recent ETL Jobs</CardTitle>
          <CardDescription>Data upload and processing history</CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Status</TableHead>
                <TableHead>HEI</TableHead>
                <TableHead>Domain</TableHead>
                <TableHead>Total Rows</TableHead>
                <TableHead>Success</TableHead>
                <TableHead>Errors</TableHead>
                <TableHead>Uploaded By</TableHead>
                <TableHead>Date</TableHead>
                <TableHead>Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {mockETLJobs.map((job) => (
                <TableRow key={job.id}>
                  <TableCell>
                    <div className="flex items-center gap-2">
                      {getStatusIcon(job.status)}
                      <Badge variant={
                        job.status === 'Success' ? 'default' :
                        job.status === 'Failed' ? 'destructive' : 'secondary'
                      }>
                        {job.status}
                      </Badge>
                    </div>
                  </TableCell>
                  <TableCell className="max-w-xs truncate">{job.heiName}</TableCell>
                  <TableCell>
                    <Badge variant="outline">{job.domain}</Badge>
                  </TableCell>
                  <TableCell>{job.totalRows}</TableCell>
                  <TableCell className="text-green-600">{job.successRows}</TableCell>
                  <TableCell className={job.errorRows > 0 ? 'text-red-600' : ''}>
                    {job.errorRows}
                  </TableCell>
                  <TableCell>{job.uploadedBy}</TableCell>
                  <TableCell>
                    {new Date(job.createdAt).toLocaleDateString()}
                  </TableCell>
                  <TableCell>
                    <div className="flex gap-2">
                      <Button variant="ghost" size="sm">
                        <Eye className="w-4 h-4" />
                      </Button>
                      {job.status === 'Failed' && (
                        <Button variant="ghost" size="sm">
                          <RefreshCw className="w-4 h-4" />
                        </Button>
                      )}
                    </div>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      {/* Error Details for Failed Jobs */}
      {mockETLJobs.filter(j => j.status === 'Failed').length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle>Error Details</CardTitle>
            <CardDescription>Review errors from failed ETL jobs</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {mockETLJobs.filter(j => j.status === 'Failed').map((job) => (
                <div key={job.id} className="p-4 bg-red-50 rounded-lg border border-red-200">
                  <div className="flex items-start justify-between mb-2">
                    <div>
                      <p className="text-sm">{job.heiName} - {job.domain}</p>
                      <p className="text-xs text-gray-500">
                        {new Date(job.createdAt).toLocaleString()}
                      </p>
                    </div>
                    <Button size="sm" variant="outline">
                      <RefreshCw className="w-4 h-4 mr-2" />
                      Reprocess
                    </Button>
                  </div>
                  <div className="space-y-1 mt-3">
                    {job.errors.map((error, index) => (
                      <p key={index} className="text-sm text-red-700">• {error}</p>
                    ))}
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      )}
    </div>
  );
}
