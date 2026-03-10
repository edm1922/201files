# CSC Document Management System — Developer A Task Tracker

This document isolates all tasks assigned to **Developer A** across all development phases to prevent overlap and merge conflicts with Developer B. Developer A is primarily responsible for the Employee domain, Upload/Storage backend logic, Search backend, and System architecture (Scheduled Tasks, Backups, Concurrency).

---

## Phase 2: Core CRUD (Week 3–4)

- [X] 2.5 Companies CRUD
- [X] 2.6 Departments CRUD (for categorizing doc types)
- [ ] 2.7 Physical Locations management (admin CRUD for cabinets/racks)
- [ ] 2.8 Document Types management (admin view + logic)
- [ ] 2.9 Global Document Requirements settings (admin settings page to toggle required doc types)

*Expected Outcome: System administrators have full control over the structural parameters (companies, locations, and global requirements).*

---

## Phase 3: Document Upload & Storage (Week 5–6)

- [ ] 3.3 Document Upload Controller & Storage Logic (handling file ingestion)
- [ ] 3.4 Multi-file → single PDF Merging Service
- [ ] 3.5 Storage system (save to `storage/app/documents/{system_id}/` & log)
- [ ] 3.6 Archive system (encoder soft-deletes + admin restore logic)

*Expected Outcome: The backend successfully accepts files from the UI, merges them if multiple files are provided, securely saves the document to the correct partition, and logs the action.*

---

## Phase 4: Search & Retrieval (Week 7–8)

- [ ] 4.3 Meilisearch backend setup & Scout Integration
- [ ] 4.4 Searchable models (syncing Employee + Document data to Meilisearch index)
- [ ] 4.5 Backend Search Controller & Queries (executing Meilisearch queries received from UI)

*Expected Outcome: The database seamlessly syncs with Meilisearch, and an API/Controller is available to receive facet filters from the UI and return lightning-fast results.*

---

## Phase 5: Reports & Dashboard (Week 9–10)

- [ ] 5.4 Dashboard & Reports Controllers (Data Aggregation queries)
- [ ] 5.5 Export Service (generating Excel + PDF reports from data)
- [ ] 5.6 Scheduled Tasks (Cron) for Expiry Checks (daily job to flag expired documents)

*Expected Outcome: Data endpoints are available for the Dashboard widgets. Complex SQL queries for missing/expired docs are optimized. Excel and PDF generation works.*

---

## Phase 6: Polish & Security (Week 11–12)

- [ ] 6.3 Concurrency & Transactions (DB locks, unique constraints backend)
- [ ] 6.4 Backup Service (schedule `spatie/laravel-backup` to local/external)
- [ ] 6.5 Performance Testing & Optimization (with Dev B)
- [ ] 6.6 Production Server Deployment (with Dev B)

*Expected Outcome: Database handles concurrent requests safely. Nightly backups are automated.*
