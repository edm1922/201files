# Documentation Standards

Clear documentation ensures that both current developers and future maintainers can easily navigate the project.

## 1. Code Comments (The "Why", Not the "What")
Do not state the obvious. Write comments that explain the business reasoning, edge cases, or "why" a particular approach was taken.

**BAD:**
```php
// Foreach over the users
foreach ($users as $user) {
    // Add 1 to count
    $count++;
}
```

**GOOD:**
```php
// Filtering manually here instead of DB because the legacy XYZ condition 
// cannot be translated into a standard SQL WHERE clause.
foreach ($users as $user) { ... }
```

## 2. DocBlocks
Use DocBlocks on complex classes, Service methods, and logic that isn't immediately self-documenting. Define parameter types and return types.

```php
/**
 * Merges multiple image/pdf paths into a single PDF document.
 * 
 * @param array $filePaths Array of absolute local file paths.
 * @param string $outputName The target filename (e.g. EMP-001_SSS.pdf).
 * @return string The absolute path of the uniquely generated merged PDF.
 * @throws DocumentMergeException If an invalid file type is provided.
 */
public function mergeDocuments(array $filePaths, string $outputName): string
```

## 3. Commit Messages
Commit messages should be descriptive and grouped by feature/phase.
- Format: `[Phase X] Action performed (Brief description)`
- Example: `[Phase 2] Add Employee CRUD and deployment tracking`

## 4. Living Documentation
- Keep the `README.md` updated with setup instructions, prerequisite PHP extensions (e.g., `ext-zip`, `ext-imagick`), and Meilisearch installation instructions.
- Ensure the `task.md` (Checklist) and `implementation_plan.md` in the `.gemini/brain` directory reflect the real-time truth of the project's progress.
