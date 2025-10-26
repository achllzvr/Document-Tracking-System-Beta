// Mock data for Front-end First phase
// Note: This mirrors the shapes from src/data/mockData.ts and extends for PHP pages

export const regions = [
  'NCR', 'CAR', 'Region I', 'Region II', 'Region III', 'Region IV-A', 'Region IV-B',
  'Region V', 'Region VI', 'Region VII', 'Region VIII', 'Region IX', 'Region X',
  'Region XI', 'Region XII', 'Region XIII', 'BARMM'
];

export const institutionTypes = [
  'State University', 'State College', 'Private University', 'Private College',
  'Local University', 'Local College'
];

export const ownershipForms = ['Public', 'Private', 'Sectarian', 'Non-Sectarian'];

export const heis = [
  { id: 1, name: 'University of the Philippines', shortName: 'UP', region: 'NCR', municipality: 'Quezon City', institutionType: 'State University', ownershipForm: 'Public', headName: 'Dr. Juan Dela Cruz', headTitle: 'President', email: 'info@up.edu.ph', phone: '+63 2 8981 8500', address: 'Diliman, Quezon City, Metro Manila', status: 'Active' },
  { id: 2, name: 'Ateneo de Manila University', shortName: 'ADMU', region: 'NCR', municipality: 'Quezon City', institutionType: 'Private University', ownershipForm: 'Private', headName: 'Fr. Roberto Yap', headTitle: 'President', email: 'info@ateneo.edu', phone: '+63 2 8426 6001', address: 'Loyola Heights, Quezon City, Metro Manila', status: 'Active' },
  { id: 3, name: 'De La Salle University', shortName: 'DLSU', region: 'NCR', municipality: 'Manila', institutionType: 'Private University', ownershipForm: 'Private', headName: 'Br. Armin Luistro', headTitle: 'President', email: 'info@dlsu.edu.ph', phone: '+63 2 8524 4611', address: 'Taft Avenue, Manila', status: 'Active' },
  { id: 4, name: 'University of Santo Tomas', shortName: 'UST', region: 'NCR', municipality: 'Manila', institutionType: 'Private University', ownershipForm: 'Private', headName: 'Fr. Richard Ang', headTitle: 'Rector', email: 'info@ust.edu.ph', phone: '+63 2 8731 3101', address: 'España Boulevard, Manila', status: 'Active' },
  { id: 5, name: 'Polytechnic University of the Philippines', shortName: 'PUP', region: 'NCR', municipality: 'Manila', institutionType: 'State University', ownershipForm: 'Public', headName: 'Dr. Emanuel de Guzman', headTitle: 'President', email: 'info@pup.edu.ph', phone: '+63 2 8335 1PUP', address: 'Sta. Mesa, Manila', status: 'Active' }
];

export const tickets = [
  { id: 101, heiId: 1, heiName: 'University of the Philippines', title: 'Q1 2025 Enrollment Data Submission', category: 'Enrollment', priority: 'High', status: 'Open', assigneeId: 9001, assigneeName: 'Juan Dela Cruz', dueDate: '2025-10-20', description: 'Submit the enrollment data for Q1 2025 using the latest template.', createdBy: 'Maria Santos (CHED)', createdAt: '2025-10-01T08:00:00Z', updatedAt: '2025-10-11T10:30:00Z' },
  { id: 102, heiId: 2, heiName: 'Ateneo de Manila University', title: 'Faculty Data Update Required', category: 'Faculty', priority: 'Medium', status: 'Open', assigneeId: 9002, assigneeName: 'Maria Garcia', dueDate: '2025-10-25', description: 'Update faculty information including new hires and departures.', createdBy: 'Maria Santos (CHED)', createdAt: '2025-10-05T09:00:00Z', updatedAt: '2025-10-05T09:00:00Z' },
  { id: 103, heiId: 1, heiName: 'University of the Philippines', title: 'Graduates Data SY 2024-2025', category: 'Graduates', priority: 'High', status: 'In Progress', assigneeId: 9003, assigneeName: 'Ana Reyes', dueDate: '2025-10-18', description: 'Submit graduates data for School Year 2024-2025.', createdBy: 'Maria Santos (CHED)', createdAt: '2025-10-08T11:00:00Z', updatedAt: '2025-10-08T11:00:00Z' },
  { id: 104, heiId: 3, heiName: 'De La Salle University', title: 'Institutional Profile Update', category: 'Institutional Profile', priority: 'Low', status: 'Pending', assigneeId: 9004, assigneeName: 'Pedro Santos', dueDate: '2025-10-30', description: 'Review and update institutional profile.', createdBy: 'Maria Santos (CHED)', createdAt: '2025-09-15T14:00:00Z', updatedAt: '2025-10-10T16:00:00Z' },
  { id: 105, heiId: 4, heiName: 'University of Santo Tomas', title: 'Enrollment Validation Issues', category: 'Enrollment', priority: 'Urgent', status: 'In Progress', assigneeId: 9005, assigneeName: 'Rosa Martinez', dueDate: '2025-10-15', description: 'Validation errors in last enrollment submission. Please correct and resubmit.', createdBy: 'Maria Santos (CHED)', createdAt: '2025-10-09T13:00:00Z', updatedAt: '2025-10-11T09:00:00Z' }
];

export const comments = [
  { id: 1, ticketId: 101, userId: 1, userName: 'Maria Santos (CHED)', userRole: 'CHED', content: 'Please prioritize this submission.', createdAt: '2025-10-11T10:30:00Z' },
  { id: 2, ticketId: 101, userId: 9001, userName: 'Juan Dela Cruz', userRole: 'HEI_HEAD', content: 'Received. Working on it.', createdAt: '2025-10-11T11:00:00Z' },
];

export const templates = [
  { id: 201, name: 'Enrollment Template v1', category: 'Enrollment', version: '1.0', file: './assets/templates/enrollment_v1.xlsx' },
  { id: 202, name: 'Faculty Template v1', category: 'Faculty', version: '1.0', file: './assets/templates/faculty_v1.xlsx' },
];

// etlJobs removed

export const notifications = [
  { id: 'notif-1', userId: 'ched-1', type: 'comment', title: 'New Comment', message: 'Juan Dela Cruz commented on ticket "Q1 2025 Enrollment Data Submission"', read: false, createdAt: '2025-10-11T10:30:00Z', link: './ticket-details.php?ticket_id=101' }
];

export const employmentCodes = ['Full-time', 'Part-time', 'Contractual', 'Visiting'];
export const degrees = ['PhD', 'Masters', 'Bachelors', 'Associate'];
export const disciplines = ['Computer Science', 'Engineering', 'Business', 'Education', 'Medicine', 'Law', 'Arts', 'Sciences', 'Social Sciences', 'Humanities'];
export const genders = ['Male', 'Female'];

// Mock domain data for HEI data-entry pages
export const enrollmentData = [
  // heiId, academic year (YYYY), term (1/2), program, major, yearLevel, sex ('m'|'f'), totalCount
  { id: 1, heiId: 1, acadYear: 2025, term: '1', program: 'BS Computer Science', major: '', yearLevel: 1, sex: 'm', totalCount: 120, createdAt: '2025-10-01T09:00:00Z' },
  { id: 2, heiId: 1, acadYear: 2025, term: '1', program: 'BS Computer Science', major: '', yearLevel: 1, sex: 'f', totalCount: 110, createdAt: '2025-10-01T09:00:00Z' },
];

export const facultyData = [
  // heiId, name, employmentType, gender, primaryTeaching (degree), highestDegree, discipline
  { id: 1, heiId: 1, name: 'Prof. Juan dela Cruz', employmentType: 'Full-time', gender: 'Male', primaryTeaching: 'Bachelors', highestDegree: 'Masters', discipline: 'Computer Science', createdAt: '2025-10-01T09:00:00Z' },
  { id: 2, heiId: 1, name: 'Dr. Maria Santos', employmentType: 'Full-time', gender: 'Female', primaryTeaching: 'Masters', highestDegree: 'PhD', discipline: 'Engineering', createdAt: '2025-10-02T10:00:00Z' },
];

export const graduatesData = [
  // heiId, name, sex ('m'|'f'), date, program, major
  { id: 1, heiId: 1, name: 'Ana Reyes', sex: 'f', date: '2025-06-15', program: 'BS Computer Science', major: '', createdAt: '2025-06-15T10:00:00Z' },
  { id: 2, heiId: 1, name: 'Mark Cruz', sex: 'm', date: '2025-06-15', program: 'BS Computer Science', major: '', createdAt: '2025-06-15T10:00:00Z' },
];

// HEI sub-users (Head manages these). Active indicates enabled/disabled.
export const heiUsers = [
  { id: 9001, heiId: 1, name: 'Juan Dela Cruz', email: 'juan.delacruz@up.edu.ph', role: 'Registrar', active: true, createdAt: '2025-09-20T09:00:00Z' },
  { id: 9002, heiId: 1, name: 'Maria Santos', email: 'maria.santos@up.edu.ph', role: 'HR', active: true, createdAt: '2025-09-22T10:00:00Z' },
  { id: 9003, heiId: 1, name: 'Ana Reyes', email: 'ana.reyes@up.edu.ph', role: 'Uploader', active: false, createdAt: '2025-09-25T11:00:00Z' },
];

export function paginate(items, page, perPage) {
  const total = items.length;
  const pages = Math.max(1, Math.ceil(total / perPage));
  const clamped = Math.min(Math.max(1, page), pages);
  const start = (clamped - 1) * perPage;
  const end = start + perPage;
  return { items: items.slice(start, end), page: clamped, pages, total };
}
