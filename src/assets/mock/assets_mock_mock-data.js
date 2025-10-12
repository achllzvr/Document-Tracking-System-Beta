
// Mock data for front-end phase only. Replace with backend calls later.

// Institutions
export const heis = [
  { hei_ID: 101, inst_name: "University of Example", inst_region: 1, inst_type: 1, ownership_form: 1, created_at: "2025-09-15 08:00:00" },
  { hei_ID: 102, inst_name: "Polytechnic Sample College", inst_region: 2, inst_type: 2, ownership_form: 2, created_at: "2025-09-20 10:30:00" }
];

// HEI Users
export const heiUsers = [
  { hei_user_ID: 201, hei_ID: 101, name: "Alice Reyes", role: "Head", email: "alice@example.edu" },
  { hei_user_ID: 202, hei_ID: 101, name: "Bob Cruz", role: "Sub-User", role_name: "Registrar", email: "bob@example.edu" }
];

// Tickets
export const tickets = [
  { ticket_id: 501, title: "Enrollment AY 2025 Term 1", category: "Enrollment", priority: "High", status: "Open", due_date: "2025-10-25", hei_name: "University of Example", created_at: "2025-10-01 09:11:00", updated_at: "2025-10-05 07:00:00", assignee_hei_user_ID: 202 },
  { ticket_id: 502, title: "Faculty Profile Update", category: "Faculty", priority: "Medium", status: "Pending - Designated", due_date: "2025-11-05", hei_name: "Polytechnic Sample College", created_at: "2025-10-02 10:00:00", updated_at: "2025-10-02 10:00:00", assignee_hei_user_ID: 201 }
];

// Ticket comments
export const comments = {
  501: [
    { id: 1, who: "CHED", text: "Please ensure programs are up to date.", at: "2025-10-03 13:20:00" },
    { id: 2, who: "HEI", text: "Acknowledged, working on it.", at: "2025-10-03 14:05:00" }
  ]
};

// Templates
export const templates = [
  { id: 301, domain: "Enrollment", name: "Enrollment Template v1", file_rel_path: "storage/templates/enrollment_v1.xlsx" },
  { id: 302, domain: "Faculty", name: "Faculty Template v2", file_rel_path: "storage/templates/faculty_v2.xlsx" }
];

// ETL jobs
export const etlJobs = [
  { id: 801, ticket_ID: 501, hei_user_ID: 202, hei_ID: 101, domain: "Enrollment", original_filename: "enrollment_ay2025_term1.xlsx", stored_rel_path: "storage/hei-uploads/uoe/ticket_501_20251005.xlsx", status: "COMPLETED", rows_inserted: 540, rows_updated: 0, rows_failed: 0, created_at: "2025-10-05 08:40:00" }
];

// Code tables (subset)
export const regions = [
  { region_ID: 1, region_number: "01", region_division: "Region A" },
  { region_ID: 2, region_number: "02", region_division: "Region B" }
];

export const employmentCodes = [
  { employment_code: 1, employment_desc: "Full-time" },
  { employment_code: 2, employment_desc: "Part-time" }
];

// Calendar events
export const calendarEvents = [
  { calendar_ID: 901, hei_user_ID: 202, title: "Meeting with Registrar", date: "2025-10-10", type: "MEETING", desc: "Discuss enrollment data" }
];

// Notifications
export const notifications = [
  { id: 1001, target_type: "CHED", title: "New Ticket: Enrollment AY 2025", body: "Due Oct 25, 2025", link: "./view-tickets.php?ticket_id=501", is_read: 0, created_at: "2025-10-01 09:12:00" },
  { id: 1002, target_type: "HEI_USER", title: "Ticket Assigned: Faculty Profile Update", body: "Due Nov 5, 2025", link: "../HEI/ticket-details.php?ticket_id=502", is_read: 0, created_at: "2025-10-02 10:00:00" }
];

// Helper for pagination (front-end mock)
export function paginate(list, page = 1, perPage = 10) {
  const total = list.length;
  const pages = Math.max(1, Math.ceil(total / perPage));
  const clamped = Math.min(Math.max(1, page), pages);
  const start = (clamped - 1) * perPage;
  return { items: list.slice(start, start + perPage), total, pages, page: clamped, perPage };
}