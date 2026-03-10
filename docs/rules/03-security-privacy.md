# Security & Privacy Guidelines

As a cooperative handling sensitive PII (Personally Identifiable Information), strict adherence to the **Data Privacy Act (DPA) of 2012** is mandatory. 

## 1. Audit Trail (DPA Compliance)
- **Log EVERYTHING**: Every significant viewing, downloading, creating, updating, or deleting of records MUST be logged.
- **Use the Audit Service**: Call `AuditService::log()` or `AuditService::logDocument()` for relevant controller actions.
- **Read-Only Logs**: Audit logs are append-only. They do not have an `updated_at` column and must never be altered or deleted.

## 2. Role-Based Access Control (RBAC)
- **Strict Middleware**: Protect all routes using the `EnsureUserHasRole` middleware (alias: `role`).
  ```php
  // Only admins can access settings
  Route::get('/settings', [SettingsController::class, 'index'])->middleware('role:admin');
  ```
- **UI Element Hiding**: Do not show buttons or links if the user doesn't have permission to use them. Wrap them in `@if(Auth::user()->isAdmin())` Blade directives.
- **Viewer Restrictions**: The `viewer` role must NEVER be able to perform `POST`, `PUT`, `PATCH`, or `DELETE` requests that modify documents or employee records.

## 3. Secure File Delivery
- Documents stored in `storage/app/documents` are NOT publicly accessible. 
- **Never symlink the documents folder to public**. 
- To serve a document, create a controller method that checks auth and permissions, logs the view/download via `AuditLog`, and then returns the file securely using `response()->file()` or `response()->download()`.

## 4. Web Vulnerability Protections
- **Mass Assignment**: Only allow specific fields in `$fillable` arrays on models to prevent malicious users from updating protected continuous columns (e.g., `role`).
- **XSS Prevention**: Always use double curly braces `{{ $data }}` in Blade templates to escape output. Use `{!! $data !!}` only when absolutely certain the HTML is safe and sanitized.
- **CSRF Token**: Ensure all HTML forms contain the `@csrf` directive.

## 5. Soft Deletes
- Documents and Employees are never actually deleted from the database. Rely entirely on Laravel's `SoftDeletes` trait. This guarantees no historical data or audit trails are broken.
