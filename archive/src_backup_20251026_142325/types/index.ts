// User types
export type UserRole = 'CHED' | 'HEI_HEAD' | 'HEI_SUB_USER';

export interface User {
  id: string;
  username: string;
  role: UserRole;
  heiId?: string;
  heiName?: string;
  firstName: string;
  lastName: string;
}

// HEI types
export interface HEI {
  id: string;
  name: string;
  shortName: string;
  region: string;
  municipality: string;
  institutionType: string;
  ownershipForm: string;
  headName: string;
  headTitle: string;
  email: string;
  phone: string;
  address: string;
  status: 'Active' | 'Inactive';
}

// Ticket types
export type TicketStatus = 'Open' | 'In Progress' | 'Pending Review' | 'Resolved' | 'Closed';
export type TicketPriority = 'Low' | 'Medium' | 'High' | 'Urgent';
export type TicketCategory = 'Enrollment' | 'Faculty' | 'Graduates' | 'Institutional Profile' | 'Other';

export interface Ticket {
  id: string;
  heiId: string;
  heiName: string;
  title: string;
  category: TicketCategory;
  priority: TicketPriority;
  status: TicketStatus;
  assigneeId: string;
  assigneeName: string;
  dueDate: string;
  description: string;
  createdBy: string;
  createdAt: string;
  updatedAt: string;
}

export interface Comment {
  id: string;
  ticketId: string;
  userId: string;
  userName: string;
  userRole: UserRole;
  content: string;
  createdAt: string;
}

// Data types
export interface EnrollmentData {
  id: string;
  heiId: string;
  academicYear: string;
  term: string;
  program: string;
  sex: 'Male' | 'Female';
  yearLevel: string;
  count: number;
  createdAt: string;
  updatedAt: string;
}

export interface FacultyData {
  id: string;
  heiId: string;
  name: string;
  employmentType: string;
  degree: string;
  discipline: string;
  sex: 'Male' | 'Female';
  createdAt: string;
  updatedAt: string;
}

export interface GraduateData {
  id: string;
  heiId: string;
  name: string;
  sex: 'Male' | 'Female';
  graduationDate: string;
  academicYear: string;
  program: string;
  major: string;
  createdAt: string;
  updatedAt: string;
}

// ETL types
export interface ETLJob {
  id: string;
  heiId: string;
  heiName: string;
  ticketId?: string;
  domain: 'Enrollment' | 'Faculty' | 'Graduates';
  status: 'Success' | 'Failed' | 'Partial';
  totalRows: number;
  successRows: number;
  errorRows: number;
  errors: string[];
  uploadedBy: string;
  createdAt: string;
}

export interface Notification {
  id: string;
  userId: string;
  type: 'ticket' | 'comment' | 'etl' | 'status';
  title: string;
  message: string;
  read: boolean;
  createdAt: string;
  link?: string;
}
