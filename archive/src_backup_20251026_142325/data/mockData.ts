import { HEI, Ticket, EnrollmentData, FacultyData, GraduateData, Notification } from '../types';

export const mockHEIs: HEI[] = [
  {
    id: 'hei-1',
    name: 'University of the Philippines',
    shortName: 'UP',
    region: 'NCR',
    municipality: 'Quezon City',
    institutionType: 'State University',
    ownershipForm: 'Public',
    headName: 'Dr. Juan Dela Cruz',
    headTitle: 'President',
    email: 'info@up.edu.ph',
    phone: '+63 2 8981 8500',
    address: 'Diliman, Quezon City, Metro Manila',
    status: 'Active'
  },
  {
    id: 'hei-2',
    name: 'Ateneo de Manila University',
    shortName: 'ADMU',
    region: 'NCR',
    municipality: 'Quezon City',
    institutionType: 'Private University',
    ownershipForm: 'Private',
    headName: 'Fr. Roberto Yap',
    headTitle: 'President',
    email: 'info@ateneo.edu',
    phone: '+63 2 8426 6001',
    address: 'Loyola Heights, Quezon City, Metro Manila',
    status: 'Active'
  },
  {
    id: 'hei-3',
    name: 'De La Salle University',
    shortName: 'DLSU',
    region: 'NCR',
    municipality: 'Manila',
    institutionType: 'Private University',
    ownershipForm: 'Private',
    headName: 'Br. Armin Luistro',
    headTitle: 'President',
    email: 'info@dlsu.edu.ph',
    phone: '+63 2 8524 4611',
    address: 'Taft Avenue, Manila',
    status: 'Active'
  },
  {
    id: 'hei-4',
    name: 'University of Santo Tomas',
    shortName: 'UST',
    region: 'NCR',
    municipality: 'Manila',
    institutionType: 'Private University',
    ownershipForm: 'Private',
    headName: 'Fr. Richard Ang',
    headTitle: 'Rector',
    email: 'info@ust.edu.ph',
    phone: '+63 2 8731 3101',
    address: 'España Boulevard, Manila',
    status: 'Active'
  },
  {
    id: 'hei-5',
    name: 'Polytechnic University of the Philippines',
    shortName: 'PUP',
    region: 'NCR',
    municipality: 'Manila',
    institutionType: 'State University',
    ownershipForm: 'Public',
    headName: 'Dr. Emanuel de Guzman',
    headTitle: 'President',
    email: 'info@pup.edu.ph',
    phone: '+63 2 8335 1PUP',
    address: 'Sta. Mesa, Manila',
    status: 'Active'
  }
];

export const mockTickets: Ticket[] = [
  {
    id: 'tick-1',
    heiId: 'hei-1',
    heiName: 'University of the Philippines',
    title: 'Q1 2025 Enrollment Data Submission',
    category: 'Enrollment',
    priority: 'High',
    status: 'In Progress',
    assigneeId: 'hei-h-1',
    assigneeName: 'Juan Dela Cruz',
    dueDate: '2025-10-20',
    description: 'Please submit the enrollment data for Q1 2025 using the latest template.',
    createdBy: 'Maria Santos (CHED)',
    createdAt: '2025-10-01T08:00:00Z',
    updatedAt: '2025-10-11T10:30:00Z'
  },
  {
    id: 'tick-2',
    heiId: 'hei-2',
    heiName: 'Ateneo de Manila University',
    title: 'Faculty Data Update Required',
    category: 'Faculty',
    priority: 'Medium',
    status: 'Open',
    assigneeId: 'hei-h-2',
    assigneeName: 'Maria Garcia',
    dueDate: '2025-10-25',
    description: 'Update faculty information including new hires and departures.',
    createdBy: 'Maria Santos (CHED)',
    createdAt: '2025-10-05T09:00:00Z',
    updatedAt: '2025-10-05T09:00:00Z'
  },
  {
    id: 'tick-3',
    heiId: 'hei-1',
    heiName: 'University of the Philippines',
    title: 'Graduates Data SY 2024-2025',
    category: 'Graduates',
    priority: 'High',
    status: 'Open',
    assigneeId: 'hei-u-1',
    assigneeName: 'Ana Reyes',
    dueDate: '2025-10-18',
    description: 'Submit graduates data for School Year 2024-2025.',
    createdBy: 'Maria Santos (CHED)',
    createdAt: '2025-10-08T11:00:00Z',
    updatedAt: '2025-10-08T11:00:00Z'
  },
  {
    id: 'tick-4',
    heiId: 'hei-3',
    heiName: 'De La Salle University',
    title: 'Institutional Profile Update',
    category: 'Institutional Profile',
    priority: 'Low',
    status: 'Pending Review',
    assigneeId: 'hei-h-3',
    assigneeName: 'Pedro Santos',
    dueDate: '2025-10-30',
    description: 'Please review and update institutional profile information.',
    createdBy: 'Maria Santos (CHED)',
    createdAt: '2025-09-15T14:00:00Z',
    updatedAt: '2025-10-10T16:00:00Z'
  },
  {
    id: 'tick-5',
    heiId: 'hei-4',
    heiName: 'University of Santo Tomas',
    title: 'Enrollment Validation Issues',
    category: 'Enrollment',
    priority: 'Urgent',
    status: 'In Progress',
    assigneeId: 'hei-h-4',
    assigneeName: 'Rosa Martinez',
    dueDate: '2025-10-15',
    description: 'There are validation errors in the last enrollment data submission. Please correct and resubmit.',
    createdBy: 'Maria Santos (CHED)',
    createdAt: '2025-10-09T13:00:00Z',
    updatedAt: '2025-10-11T09:00:00Z'
  }
];

export const mockEnrollmentData: EnrollmentData[] = [
  {
    id: 'enr-1',
    heiId: 'hei-1',
    academicYear: '2024-2025',
    term: '1st Semester',
    program: 'BS Computer Science',
    sex: 'Male',
    yearLevel: '1st Year',
    count: 145,
    createdAt: '2025-09-01T08:00:00Z',
    updatedAt: '2025-09-01T08:00:00Z'
  },
  {
    id: 'enr-2',
    heiId: 'hei-1',
    academicYear: '2024-2025',
    term: '1st Semester',
    program: 'BS Computer Science',
    sex: 'Female',
    yearLevel: '1st Year',
    count: 98,
    createdAt: '2025-09-01T08:00:00Z',
    updatedAt: '2025-09-01T08:00:00Z'
  }
];

export const mockFacultyData: FacultyData[] = [
  {
    id: 'fac-1',
    heiId: 'hei-1',
    name: 'Dr. Jose Rizal',
    employmentType: 'Full-time',
    degree: 'PhD',
    discipline: 'Computer Science',
    sex: 'Male',
    createdAt: '2025-01-15T08:00:00Z',
    updatedAt: '2025-01-15T08:00:00Z'
  }
];

export const mockGraduateData: GraduateData[] = [
  {
    id: 'grad-1',
    heiId: 'hei-1',
    name: 'Maria Clara Santos',
    sex: 'Female',
    graduationDate: '2025-06-15',
    academicYear: '2024-2025',
    program: 'BS Computer Science',
    major: 'Software Engineering',
    createdAt: '2025-06-20T08:00:00Z',
    updatedAt: '2025-06-20T08:00:00Z'
  }
];

// mockETLJobs removed

export const mockNotifications: Notification[] = [
  {
    id: 'notif-1',
    userId: 'ched-1',
    type: 'comment',
    title: 'New Comment',
    message: 'Juan Dela Cruz commented on ticket "Q1 2025 Enrollment Data Submission"',
    read: false,
    createdAt: '2025-10-11T10:30:00Z',
    link: '/ched/ticket-details/tick-1'
  },
  // ETL notification removed
];

export const mockRegions = [
  'NCR', 'CAR', 'Region I', 'Region II', 'Region III', 'Region IV-A', 'Region IV-B',
  'Region V', 'Region VI', 'Region VII', 'Region VIII', 'Region IX', 'Region X',
  'Region XI', 'Region XII', 'Region XIII', 'BARMM'
];

export const mockInstitutionTypes = [
  'State University', 'State College', 'Private University', 'Private College',
  'Local University', 'Local College'
];

export const mockOwnershipForms = [
  'Public', 'Private', 'Sectarian', 'Non-Sectarian'
];

export const mockEmploymentTypes = [
  'Full-time', 'Part-time', 'Contractual', 'Visiting'
];

export const mockDegrees = [
  'PhD', 'Masters', 'Bachelors', 'Associate'
];

export const mockDisciplines = [
  'Computer Science', 'Engineering', 'Business', 'Education', 'Medicine',
  'Law', 'Arts', 'Sciences', 'Social Sciences', 'Humanities'
];
