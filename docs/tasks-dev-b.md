# CSC Document Management System — Developer B Task Tracker

This document isolates all tasks assigned to **Developer B** across all development phases to prevent overlap and merge conflicts with Developer A. Developer B is primarily responsible for the Company boundaries, Admin Settings, Frontend Upload UI, Search UI, and Reporting layout.

---

## Phase 2: Core CRUD (Week 3–4)
- [ ] 2.5 Companies CRUD
- [ ] 2.6 Departments CRUD (for categorizing doc types)
- [ ] 2.7 Physical Locations management (admin CRUD for cabinets/racks)
- [ ] 2.8 Document Types management (admin view + logic)
- [ ] 2.9 Global Document Requirements settings (admin settings page to toggle required doc types)

*Expected Outcome: System administrators have full control over the structural parameters (companies, locations, and global requirements).*

---

## Phase 3: Document Upload & Storage (Week 5–6)
- [ ] 3.1 Single Page Upload UI (form to select doc type, dates, and physical location)
- [ ] 3.2 Multi-file UI (Frontend) (drag & drop interface for multiple images/PDFs)
- [ ] 3.3 Document viewer UI (in-browser PDF/image rendering that pings the backend for file retrieval)

*Expected Outcome: The user experience for uploading and viewing documents is frictionless. The UI properly captures necessary metadata before sending it to Dev A's upload API.*

---

## Phase 4: Search & Retrieval (Week 7–8)
- [ ] 4.1 Search UI Frontend (full-page search with sidebar facets: Company, Doc Type, Status, Expiry)
- [ ] 4.2 Barcode lookup UI (scanner input field & visual feedback bridging to profile)

*Expected Outcome: The main search interface visually mimics the CENTRO style, actively pinging Dev A's Meilisearch controller to live-update results.*

---

## Phase 5: Reports & Dashboard (Week 9–10)
- [ ] 5.1 Dashboard widgets UI (summary cards, recent uploads frontend)
- [ ] 5.2 Missing Documents UI (tabular display of what's missing per employee vs global requirements)
- [ ] 5.3 Expiry Alert UI (tabular display of expiring documents)

*Expected Outcome: Data from Dev A's controllers is rendered cleanly in tables and charts. Export buttons trigger Dev A's export services.*

---

## Phase 6: Polish & Security (Week 11–12)
- [ ] 6.1 Data Privacy Settings UI (privacy policy, consent logs viewer)
- [ ] 6.2 Error handling UI (user-friendly error pages: 404, 403, 500)
- [ ] 6.5 Performance Testing & Optimization (with Dev A)
- [ ] 6.6 Production Server Deployment (with Dev A)

*Expected Outcome: The system elegantly handles failure states and guarantees DPA compliance visually to the administrators.*
