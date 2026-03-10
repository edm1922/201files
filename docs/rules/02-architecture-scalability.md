# Architecture & Scalability Rules

Designing for the targeted 50,000 documents (growing by ~24,000 annually) requires strict adherence to performance and scalability rules.

## 1. Database Optimization
- **Eager Loading**: Always prevent the "N+1" query problem. When fetching a list of models that will require child relationships, use `with()`.
  ```php
  // BAD: Triggers a query for EVERY document to get the uploader
  $documents = Document::all(); 
  
  // GOOD: Fetches all documents and all related users in just 2 queries
  $documents = Document::with('uploader', 'documentType')->get();
  ```
- **Indexes**: Ensure all columns heavily used in `WHERE`, `JOIN`, or `ORDER BY` clauses possess database indexes (e.g., `employee_id`, `document_type_id`, `status`). Check the initial migrations for reference.
- **Chunking**: When processing large exports or reports (e.g., iterating over 30,000 employees), use `chunk()` or `lazy()` to prevent out-of-memory errors.

## 2. Background Processing (Queues)
Never make the user wait for a slow operation to complete in the web request cycle. The following operations MUST be dispatched to Laravel Queues:
- **Batch Uploading**: Processing ZIP files or multiple large PDFs.
- **File Merging**: Combining multiple images into a single PDF.
- **OCR Processing** (Future Phase): Extracting text from documents.
- **Report Generation**: Generating massive Excel sheets or PDFs.

## 3. File Storage Strategy
- **Partitioning**: Do not dump 50,000 files into a single directory (OS limitation issues). Store documents partitioned by employee system ID:
  `storage/app/documents/{employee_system_id}/{system_filename}.pdf`
- **Naming Collisions**: Always append a timestamp when a file collision occurs (`EMP-001_SSS_20260309_143022.pdf`).

## 4. Search Implementation
- Use **Laravel Scout with Meilisearch** (Phase 4) for queries involving text search, partial matches, or multi-faceted filtering. Do not rely heavily on SQL `LIKE %search%` queries for complex dashboard searches as the dataset grows.
