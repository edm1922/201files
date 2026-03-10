# Clean Code Guidelines

To maintain a highly readable, maintainable, and bug-free codebase for the CSC Document Management System, all developers must adhere to the following Clean Code principles.

## 1. Naming Conventions
- **Variables & Methods**: `camelCase` (e.g., `$employeeRecord`, `generateReport()`)
- **Classes & Models**: `PascalCase` (e.g., `DocumentController`, `AuditLog`)
- **Database Tables**: `snake_case`, plural (e.g., `document_types`, `physical_locations`)
- **Database Columns**: `snake_case` (e.g., `system_id`, `date_received`)
- **Blade Files**: `kebab-case.blade.php` (e.g., `employee-profile.blade.php`)
- **Booleans**: Prefix with `is_`, `has_`, or `can_` (e.g., `is_active`, `has_expiry`)

## 2. Laravel MVC Responsibilities
- **Skinny Controllers**: Controllers should only handle HTTP request parsing, authorization checks, calling Services/Models, and returning a response/view. NEVER put complex business logic in controllers.
- **Fat Models & Services**: 
  - Put DB relationships, scopes, and simple data formatting (Accessors/Mutators) in Eloquent Models.
  - Put complex business logic (e.g., multi-file PDF merging, generating missing document reports) into dedicated Service classes (e.g., `DocumentService`, `ReportService`).
- **Form Requests**: Always use Form Request classes (`php artisan make:request`) for validation instead of validating directly in the controller.

## 3. Functions and Methods
- **Single Responsibility Principle (SRP)**: A function should do exactly one thing. If a function is longer than 20-30 lines, consider extracting parts of it into private helper methods.
- **Early Returns**: Return early to avoid deep nesting (Arrow Anti-Pattern).
  ```php
  // BAD
  if ($user) {
      if ($user->isActive()) {
          return $user->data;
      }
  }
  return null;

  // GOOD
  if (!$user || !$user->isActive()) {
      return null;
  }
  return $user->data;
  ```

## 4. Frontend & Views
- **Blade Components**: Break down repetitive UI elements into reusable Blade components (`<x-button>`, `<x-search-bar>`).
- **Logic-less Views**: Keep PHP logic in views to an absolute minimum. Pass pre-formatted, ready-to-display data from the Controller to the View.
- **Alpine.js for Interactivity**: Use Alpine.js for dropdowns, modals, and tabs. Keep JS closely co-located with the HTML it manipulates, avoiding detached DOM manipulation.
