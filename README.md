# CHED PRISM
CHED Portal for Repository, Insights, and Submission Management

A modern web system for CHED and Higher Education Institutions (HEIs) to request, submit, validate, analyze, and govern institutional data at scale. PRISM blends a structured data repository with ticket-driven collaboration, hybrid (manual + template-based) data submission, and rich analytics.

• CHED users: Create/manage tickets, review comments and progress, browse data across HEIs, manage templates/mappings, monitor ETL jobs, explore analytics.
• HEI users (Head/Sub-User): Work tickets, download templates, upload filled sheets for auto-ingestion, manually edit data when needed, manage organization details, and keep personal calendars.

---

## Key Features

- Ticketing & Collaboration
  - CHED creates tickets for HEIs with due dates and priorities.
  - Conversation via comments (both sides).
  - Simple status model with clear transitions.

- Hybrid Data Input (Manual + Template Upload)
  - Domain data captured in structured tables:
    - Institutional Profile
    - Enrollment
    - Faculty
    - Graduates
  - HEIs may:
    1) Manually encode data in web forms; and/or
    2) Download a CSV/XLSX template, fill it, and upload to auto-populate the DB via a configurable ETL mapping.
  - Ingestion results (rows inserted/updated/failed) are logged and summarized back to the ticket.

- Repository & Governance
  - Structured DB model with reference/code tables (e.g., regions, ownership form, degrees, disciplines).
  - Auditing timestamps for updates.
  - Optional notifications for key events.

- HEI Data Analytics (CHED)
  - Cross-HEI insights via charts, tables, and exports.
  - Filters by Region, Municipality/City, HEI, and domain-specific fields (AY/Term, Program/Major, Sex, Degree/Discipline, etc.).
  - Drill-through to underlying records.

- Calendars
  - Per-user calendars for HEI users; ticket due dates can overlay on calendar.

---

## Built to help CHED and HEIs streamline data collaboration, improve data quality, and accelerate evidence-based decision making — all in one PRISM.
