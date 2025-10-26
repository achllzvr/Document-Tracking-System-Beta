# CHED Higher Education Information System

A comprehensive web application for the Commission on Higher Education (CHED) to manage Higher Education Institutions (HEIs) and track data submissions.

## Overview

This system provides separate portals for CHED administrators and HEI users (both Head and Sub-Users) to manage institutional data and track tickets.

## Features

### Global Features
- **Login System**: Role-based authentication for CHED, HEI Head, and HEI Sub-Users
- **Notifications**: Real-time notifications for ticket updates and comments
- **Responsive Design**: Mobile-friendly interface that works on all devices

### CHED Portal Features
1. **Dashboard**: Overview of tickets, HEIs, and recent activity
2. **Institutions Management**: Browse, search, and filter HEIs by region, type, and ownership
3. **Tickets System**: Create and manage data submission tickets for HEIs
4. **Data Management**:
   - Enrollment data across all HEIs
   - Faculty data management
   - Graduates data tracking
5. **Analytics**: Interactive charts and visualizations for institutional, enrollment, faculty, and graduate data
6. **Templates & Mappings**: Manage data templates for HEI submissions
7. **Code Tables**: Reference data management

### HEI Portal Features
1. **Dashboard**: Personalized view of assigned tickets and data submission status
2. **Tickets**: View and manage assigned tickets with org-wide toggle for Head users
3. **Data Entry**:
   - Enrollment data CRUD operations
   - Faculty records management
   - Graduates data entry
4. **Institution Profile**: Edit institutional information (Head users only)
5. **Sub-Users Management**: Create and manage sub-user accounts (Head users only)
6. **Calendar**: Event management with ticket due date overlays
7. **Template Download/Upload**: Download templates and upload completed data

## Demo Credentials

### CHED Administrator
- **Username**: ched_admin
- **Password**: admin123

### HEI Head User
- **Username**: hei_head
- **Password**: head123
- **Institution**: University of the Philippines

### HEI Sub-User
- **Username**: hei_user
- **Password**: user123
- **Institution**: University of the Philippines

## Technology Stack

- **React**: UI framework
- **TypeScript**: Type-safe development
- **Tailwind CSS**: Styling and responsive design
- **shadcn/ui**: Component library
- **Recharts**: Data visualization
- **Lucide React**: Icons

## Page Structure

### CHED Area (19 pages)
- Dashboard
- Institutions List
- Institution Details
- HEI Users Management
- Tickets List
- Create Ticket
- Edit Ticket
- Ticket Details
- Enrollment Data
- Faculty Data
- Graduates Data
- Templates & Mappings
- Code Tables
- HEI Analytics

### HEI Area (10 pages)
- Dashboard
- Tickets List
- Ticket Details
- Enrollment Data Entry
- Faculty Data Entry
- Graduates Data Entry
- Institution Profile
- Sub-Users Management
- Calendar
- Upload/Download Templates

## Key Features Implemented

### Authentication & Authorization
- Role-based access control
- Persistent sessions using localStorage
- Different navigation and features per role

### Ticket Management
- Create, view, edit tickets
- Assign tickets to HEI users
- Set priority, category, due dates
- Comment system for collaboration
- Status tracking and updates

### Data Management
- CRUD operations for enrollment, faculty, and graduate data
- Filtering and search capabilities
- Export functionality
- Validation and error handling

<!-- ETL Processing section removed -->

### Analytics
- Interactive charts and graphs
- Multi-domain analysis (Institutional, Enrollment, Graduates, Faculty)
- Filter by region, HEI, academic year
- Export capabilities

## Navigation

The application uses a client-side routing system with:
- Sidebar navigation for main pages
- Breadcrumb support for nested pages
- Mobile-responsive menu with hamburger toggle
- Active page highlighting

## Notifications

Both CHED and HEI users receive notifications for:
- New ticket assignments
- Comment updates
- Status changes
<!-- ETL job completions removed -->
- Approaching due dates

## Future Enhancements

To make this a production-ready system, consider adding:
- Backend API integration (Supabase recommended)
- Real-time collaboration features
- Advanced search and filtering
- Bulk data operations
- Report generation and scheduling
- Email notifications
- File attachment support
- Audit logging
- Advanced analytics and dashboards
- Role-based permissions system
- Multi-language support

## Development

This is a frontend prototype with mock data. To extend this system:

1. **Backend Integration**: Connect to a backend API (e.g., Supabase) for:
   - User authentication
   - Data persistence
   - Real-time updates
   - File storage

2. **Enhanced Features**: 
   - Implement remaining placeholder pages
   - Add advanced filtering and search
   - Implement file upload/download
   - Add email notifications

3. **Production Readiness**:
   - Add comprehensive error handling
   - Implement data validation
   - Add security measures
   - Performance optimization
   - Accessibility improvements

## Notes

- All data in this system is currently mock data for demonstration purposes
- File upload/download features are placeholder implementations
- ETL processing simulates real ETL job behavior
- Analytics charts use sample data to demonstrate capabilities
