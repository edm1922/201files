# Contributors & Core Development Team

This document outlines the original development team and their primary contributions to the **CSC Document Management System**.

The foundation, architecture, and core features of this system were designed and implemented during the OJT / Internship program.

## Development Team

- **Ebszar A. Lapaz** - BS Information Technology from Ramon Magsaysay Memorial Colleges
- **Loyd  Oliver Pino** - BS Information Technology from Ramon Magsaysay Memorial Colleges

---

## Major Subsystems & Features Developed

The following modules were built from the ground up to ensure a robust, scalable, and secure document management environment:

### 1. Advanced Dashboard & Analytics

- Implemented real-time hiring trend computations with dynamic growth rate algorithms.
- Developed interactive Company and Bank distribution charts using Chart.js.
- Created dynamic summary metrics for quick overviews of total workforce, companies, and storage utilization.

### 2. 201 Files Management & Automation

- Built the comprehensive CRUD interface for employee 201 records.
- Implemented **Smart Folder Auto-Mapping**, allowing the system to automatically assign and track physical storage capacities based on fixed row increments (500 folders per row).
- Developed a seamless auto-generation mechanism for sequential `CSC-HR-XXXX` folder codes.

### 3. Reporting Hub & Export Engine

- Designed a unified, aesthetic Reports Hub.
- Developed customizable CSV generation for:
  - Employee Master Lists (with selectable data columns).
  - Physical Storage Utilization (computing true space remaining including archived files).
  - Document Expiry tracking.
  - Full System Activity Logs.

### 4. Search & UI Experience

- Integrated **Meilisearch** for instantaneous, typo-tolerant global search across employee records and folder codes.
- Built a modern, responsive interface utilizing Blade templates, Alpine.js, and custom CSS for a premium user experience.

### 5. Security & Auditing

- Implemented a comprehensive `ActivityLog` system that records "Before" and "After" states for critical actions across the system.
- Developed an archiving mechanism (Soft Deletes) that preserves data integrity and physical storage tracking for resigned employees without losing historical context.

---

*This system was proudly engineered to modernize and secure the organization's document management processes.*
