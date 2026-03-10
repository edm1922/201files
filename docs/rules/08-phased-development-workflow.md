# Phased Development Workflow & Division of Labor

To ensure the 2-developer team builds the CSC Document Management System efficiently without merge conflicts or duplicated effort, the project is strictly divided into incremental Phases. 

## 1. Overview of Phases
The system is being built in the following phases (subject to future modification):
- **Phase 1: Foundation Setup** (Migrations, Models, RBAC, Theming)
- **Phase 2: Core CRUD** (Employees, Companies, Document Types, Physical Locations)
- **Phase 3: Document Upload & Storage** (Local Storage, File Naming, Merging PDFs, Audit Logs)
- **Phase 4: Search & Retrieval** (Dashboard Search, Meilisearch Integration, Viewer Restrictions)
- **Phase 5: Reports & Dashboard** (Missing Docs, Expiry Alerts, Excel/PDF Exports)
- **Phase 6: Polish, Testing & Deployment** (UI/UX final audit, Stress Testing, Server Deployment)

*(Refer to `.gemini/brain/implementation_plan.md` for the deep technical breakdown of each phase).*

## 2. Division of Labor Rules
To prevent overlapping changes and GIT conflicts, tasks within each Phase must be explicitly divided using the **"Horizontal Slice"** or **"Vertical Domain"** method. 

**Vertical Domain Split (Preferred):**
- **Developer A** takes full ownership of the `Employee` domain (Controller, Views, Form Requests, Services).
- **Developer B** takes full ownership of the `Company` and `Physical Locations` domains.
*Neither developer touches the other's files during the sprint.*

**Horizontal Slice Split (For shared features):**
- **Developer A** builds the Database layer, Eloquent Models, and complex Services (e.g., `PdfMergeService`).
- **Developer B** builds the UI/UX, Blade Components, Alpine.js logic, and applies styling (using `ui-ux-pro-max` and `frontend-design` skills).

## 3. Phase Lifecycle & Documentation Rule

**CRITICAL RULE: The Phase Completion Document**

Every time a Phase is considered "finished", the team MUST create a dedicated markdown file summarizing the completion of that phase before moving to the next one. This creates a historical record of system evolution and decisions.

### Process:
1. Start the phase by referencing the `task.md` checklist.
2. Both developers complete their assigned tasks in separate feature branches (e.g., `feature/phase2-employees-devA`).
3. Merge both branches into the `dev` branch.
4. Run all tests and conduct a UI/UX audit.
5. **Create the Phase Completion Document**:
   Create a file in `docs/phases/` (e.g., `docs/phases/phase-01-completion.md`) containing:
   - What was accomplished.
   - Any deviations or changes made from the original plan.
   - Any new dependencies added (e.g., `composer require...`).
   - Any known bugs or technical debt deferred to later phases.
   - Approval signatures/confirmation from both developers.
6. Only after the Phase Completion Document is committed to the repository can the team begin work on the next phase.

## 4. Modifying Future Phases
Phases are not set in stone. If Phase 2 reveals that Phase 4 needs a complete rethink, update the `implementation_plan.md` and `task.md` accordingly before starting Phase 3. The living documentation must always reflect reality.
