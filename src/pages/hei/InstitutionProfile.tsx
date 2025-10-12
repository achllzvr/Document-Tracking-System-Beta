import React, { useState } from 'react';
import { useAuth } from '../../context/AuthContext';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../components/ui/card';
import { Button } from '../../components/ui/button';
import { Input } from '../../components/ui/input';
import { Label } from '../../components/ui/label';
import { Textarea } from '../../components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../components/ui/select';
import { Save, MapPin, Mail, Phone, Building2, User } from 'lucide-react';
import { mockRegions, mockInstitutionTypes, mockOwnershipForms } from '../../data/mockData';
import { toast } from 'sonner@2.0.3';

export function InstitutionProfile() {
  const { user } = useAuth();
  const [isEditing, setIsEditing] = useState(false);
  
  const [formData, setFormData] = useState({
    name: user?.heiName || '',
    shortName: 'UP',
    institutionType: 'State University',
    ownershipForm: 'Public',
    region: 'NCR',
    municipality: 'Quezon City',
    address: 'Diliman, Quezon City, Metro Manila',
    email: 'info@up.edu.ph',
    phone: '+63 2 8981 8500',
    headName: 'Dr. Juan Dela Cruz',
    headTitle: 'President',
    headEmail: 'president@up.edu.ph',
    headPhone: '+63 2 8981 8501'
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    toast.success('Institution profile updated successfully');
    setIsEditing(false);
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1>Institution Profile</h1>
          <p className="text-gray-600">Manage your institution's information and contact details</p>
        </div>
        
        {!isEditing ? (
          <Button onClick={() => setIsEditing(true)}>
            Edit Profile
          </Button>
        ) : (
          <div className="flex gap-2">
            <Button onClick={handleSubmit}>
              <Save className="w-4 h-4 mr-2" />
              Save Changes
            </Button>
            <Button variant="outline" onClick={() => setIsEditing(false)}>
              Cancel
            </Button>
          </div>
        )}
      </div>

      <form onSubmit={handleSubmit} className="grid lg:grid-cols-2 gap-6">
        {/* Basic Information */}
        <Card>
          <CardHeader>
            <div className="flex items-center gap-2">
              <Building2 className="w-5 h-5" />
              <CardTitle>Basic Information</CardTitle>
            </div>
            <CardDescription>Institution identity and classification</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="name">Institution Name</Label>
              <Input
                id="name"
                value={formData.name}
                onChange={(e) => setFormData({...formData, name: e.target.value})}
                disabled={!isEditing}
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="shortName">Short Name / Acronym</Label>
              <Input
                id="shortName"
                value={formData.shortName}
                onChange={(e) => setFormData({...formData, shortName: e.target.value})}
                disabled={!isEditing}
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="institutionType">Institution Type</Label>
              <Select 
                value={formData.institutionType}
                onValueChange={(value) => setFormData({...formData, institutionType: value})}
                disabled={!isEditing}
              >
                <SelectTrigger id="institutionType">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  {mockInstitutionTypes.map(type => (
                    <SelectItem key={type} value={type}>{type}</SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>

            <div className="space-y-2">
              <Label htmlFor="ownershipForm">Ownership Form</Label>
              <Select 
                value={formData.ownershipForm}
                onValueChange={(value) => setFormData({...formData, ownershipForm: value})}
                disabled={!isEditing}
              >
                <SelectTrigger id="ownershipForm">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  {mockOwnershipForms.map(form => (
                    <SelectItem key={form} value={form}>{form}</SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
          </CardContent>
        </Card>

        {/* Location */}
        <Card>
          <CardHeader>
            <div className="flex items-center gap-2">
              <MapPin className="w-5 h-5" />
              <CardTitle>Location</CardTitle>
            </div>
            <CardDescription>Physical location and address</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="region">Region</Label>
              <Select 
                value={formData.region}
                onValueChange={(value) => setFormData({...formData, region: value})}
                disabled={!isEditing}
              >
                <SelectTrigger id="region">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  {mockRegions.map(region => (
                    <SelectItem key={region} value={region}>{region}</SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>

            <div className="space-y-2">
              <Label htmlFor="municipality">Municipality / City</Label>
              <Input
                id="municipality"
                value={formData.municipality}
                onChange={(e) => setFormData({...formData, municipality: e.target.value})}
                disabled={!isEditing}
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="address">Complete Address</Label>
              <Textarea
                id="address"
                value={formData.address}
                onChange={(e) => setFormData({...formData, address: e.target.value})}
                disabled={!isEditing}
                rows={3}
              />
            </div>
          </CardContent>
        </Card>

        {/* Contact Information */}
        <Card>
          <CardHeader>
            <div className="flex items-center gap-2">
              <Mail className="w-5 h-5" />
              <CardTitle>Contact Information</CardTitle>
            </div>
            <CardDescription>General contact details</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="email">Email Address</Label>
              <Input
                id="email"
                type="email"
                value={formData.email}
                onChange={(e) => setFormData({...formData, email: e.target.value})}
                disabled={!isEditing}
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="phone">Phone Number</Label>
              <Input
                id="phone"
                type="tel"
                value={formData.phone}
                onChange={(e) => setFormData({...formData, phone: e.target.value})}
                disabled={!isEditing}
              />
            </div>
          </CardContent>
        </Card>

        {/* Institution Head */}
        <Card>
          <CardHeader>
            <div className="flex items-center gap-2">
              <User className="w-5 h-5" />
              <CardTitle>Institution Head</CardTitle>
            </div>
            <CardDescription>Details of the institution head/president</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="headName">Full Name</Label>
              <Input
                id="headName"
                value={formData.headName}
                onChange={(e) => setFormData({...formData, headName: e.target.value})}
                disabled={!isEditing}
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="headTitle">Title / Position</Label>
              <Input
                id="headTitle"
                value={formData.headTitle}
                onChange={(e) => setFormData({...formData, headTitle: e.target.value})}
                disabled={!isEditing}
                placeholder="e.g., President, Chancellor, Director"
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="headEmail">Email Address</Label>
              <Input
                id="headEmail"
                type="email"
                value={formData.headEmail}
                onChange={(e) => setFormData({...formData, headEmail: e.target.value})}
                disabled={!isEditing}
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="headPhone">Phone Number</Label>
              <Input
                id="headPhone"
                type="tel"
                value={formData.headPhone}
                onChange={(e) => setFormData({...formData, headPhone: e.target.value})}
                disabled={!isEditing}
              />
            </div>
          </CardContent>
        </Card>
      </form>

      {/* Information Notice */}
      <Card>
        <CardContent className="p-4">
          <p className="text-sm text-gray-600">
            <strong>Note:</strong> Changes to your institution profile may require verification by CHED. 
            Critical information such as institution type and ownership form changes will be reviewed before taking effect.
          </p>
        </CardContent>
      </Card>
    </div>
  );
}
