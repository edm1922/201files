# Error Handling & Logging

Proper error handling prevents user frustration and provides developers with the context needed to fix bugs quickly.

## 1. User-Facing Error Handling
- **Never expose stack traces** to the end user in production. Ensure `APP_DEBUG=false` in the production `.env`.
- **Graceful Validation**: When form validation fails, redirect back with input and clear error messages attached to the specific fields.
  ```blade
  <input type="text" name="system_id" class="form-control @error('system_id') is-invalid @enderror">
  @error('system_id')
      <div class="invalid-feedback">{{ $message }}</div>
  @enderror
  ```
- **Flash Messages**: Use session flash messages for success/error notifications on CRUD operations.
  ```php
  return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
  // OR
  return back()->with('error', 'Failed to merge PDF documents. Please check the file formats.');
  ```

## 2. System Logging (Developer Context)
- **Use Laravel's Log Facade**: For expected errors or complex background jobs (e.g., PDF merging failures, Meilisearch sync errors), log the context.
  ```php
  use Illuminate\Support\Facades\Log;

  try {
      $this->documentService->merge($files);
  } catch (MergeException $e) {
      Log::error('PDF Merge Failed', [
          'employee_id' => $employeeId,
          'files_count' => count($files),
          'error' => $e->getMessage()
      ]);
      throw $e;
  }
  ```
- **Distinguish Audit Logs from System Logs**:
  - `AuditService::log()` (Database `audit_logs` table) is for tracking *User Actions* for DPA 2012 compliance.
  - `Log::info/error()` (File `storage/logs/laravel.log`) is for tracking *System Behavior & Errors* for developers.

## 3. Exceptions
- Create Custom Exception classes for specific failure domains (e.g., `DocumentUploadException`, `OcrProcessingException`).
- Catch exceptions at the Controller or Job level to format the response gracefully, rather than letting the application crash unexpectedly.
