# Testing Strategy

To ensure long-term stability for the CSC Document Management System, automated testing is required for all critical paths.

## 1. Test Driven Development (TDD) Approach
- Write tests *before* or *alongside* feature implementation, not as an afterthought at the end of the sprint.
- Place tests in the standard Laravel `tests/Feature` and `tests/Unit` directories.

## 2. What to Test
- **Unit Tests**: Test pure logic in Services, custom rules, and complex Model accessors. (e.g., "Does the `isExpiringSoon` method correctly return true for a date 15 days from now?").
- **Feature Tests**: Test HTTP endpoints, form validation, and database state changes.
  - Assert that `admin` users can access `/settings`.
  - Assert that `viewer` users get a `403 Forbidden` when attempting to POST to `/documents`.
  - Assert that uploading a valid PDF successfully saves the file and creates a `Document` database record.

## 3. Database Testing
- Always use the `RefreshDatabase` trait in tests to ensure a clean state for every test method.
- Use Model Factories (`php artisan make:factory`) to quickly generate dummy data for tests rather than manually inserting records.

## 4. Test Naming Convention
Use descriptive test methods that explain exactly what is being tested and the expected outcome.
```php
// BAD
public function test_upload()

// GOOD
public function test_encoder_can_upload_valid_pdf_document()
public function test_viewer_cannot_upload_document_and_receives_403()
```

## 5. Running Tests
Run tests locally before every commit and merge to the `dev` branch.
```bash
php artisan test
```
Do not merge PRs that have failing tests.
