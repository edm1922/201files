# System Implementation & AI Skills Workflow

As a 2-developer team utilizing AI, follow this structured workflow to avoid merge conflicts, ensure high aesthetic quality matching the CENTRO main system, and systematically implement the phases.

## 1. Branching & Collaboration Strategy
- **`main`**: Production-ready, stable code.
- **`dev`**: The integration branch. All features merge here first.
- **Feature Branches**: Prefix with phase and developer (e.g., `feature/phase2-employees-devA`, `feature/phase2-companies-devB`).
- **Daily Sync**: Merge to `dev` at least once a day. Resolve conflicts immediately. Run `php artisan migrate:fresh --seed` to sync databases.
- **Division of Labor**: Never work on the same Controller or View simultaneously to minimize git conflicts. Refer to the split outlined in the planning phase.

## 2. AI UI/UX & Tooling Skill Usage Workflow

The global skills directory is located at `C:\Users\acer\.agents\skills`. For ANY new frontend component, page, or complex logic, strictly follow this procedure using the available skills:

### UI/UX Implementation Loop
1. **Ideation & Component Planning**
   Consult `ui-ux-pro-max/SKILL.md` before building UI elements to determine best practices for the component (e.g., accessible modals, forms, tables).
   
2. **Design & Aesthetic Application**
   Consult `frontend-design/SKILL.md` to ensure the design avoids "generic AI styling".
   - **CENTRO Theme**: We must strictly adhere to the brand Red (`#dd270d`), dark Sidebars (`#2c3340`), and Bootstrap 5 + Alpine.js layout.
   - **Visual Polish**: Focus on structured tab alignments, precise box-shadows on stat cards, and clean typography matching the main system screenshot.
   
3. **Pre-Delivery UI Audit**
   Consult `web-design-guidelines/SKILL.md`. Before merging a feature branch to `dev`, verify accessibility, responsiveness, and interaction design.

### Expanding Capabilities (Find & Create Skills)
If you encounter a repetitive task, a complex new domain (e.g., advanced OCR parsing, specialized PDF generation), or need better tools:

1. **Find Existing Skills**: Use `find-skills/SKILL.md` to search the open ecosystem (`npx skills find [query]`). If a community skill exists for the hard problem, install and use it.
2. **Create Custom Skills**: If no skill exists for a recurring project-specific need (like a custom parser for a specific Philippine government form), use `skill-creator/SKILL.md`. This allows you to define the skill, write test cases (`evals.json`), spawn subagents to verify it works, and package it (`.skill`) for the other developer to use.

## 3. Step-by-Step Implementation Loop
For each Phase listed in `task.md`:
1. **Model & Migration Update** (If needed)
2. **Controller & Service Creation**
3. **Route Definition** (Protected by `EnsureUserHasRole` middleware)
4. **View Creation** (Utilizing Blade Components and AI Skills rules)
5. **Audit Logging insertion**
6. **Local Browser Testing** 
7. **Commit & Push**

By adhering to this workflow, the UI will remain cohesive with the CENTRO system while accelerating development without stepping on each other's toes.
