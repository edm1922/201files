# Meilisearch Setup (Windows + XAMPP)

This guide is for this project (`CSC-Document-Management-System`) and explains how to run document search with Laravel Scout + Meilisearch.

## What this project already supports

The codebase is already prepared for Meilisearch:

- `Document` model is Scout-searchable.
- Department document live search uses Scout when available.
- If Meilisearch is unavailable, search falls back to SQL automatically.
- Search suggestions use minimum query length of 2 characters.
- 201 Files toolbar search can use Scout/Meilisearch with SQL fallback.
- Route names and UI labels use `meili-search` naming for clarity.

You only need to run Meilisearch and configure environment values.

## Prerequisites

- Windows machine
- XAMPP (Apache/MySQL/PHP as needed)
- Composer installed
- This project dependencies installed

## 1) Download Meilisearch

1. Open the releases page:
   - `https://github.com/meilisearch/meilisearch/releases`
2. Download the latest Windows binary (`meilisearch-windows-amd64.exe`).
3. Rename it to `meilisearch.exe`.
4. Move it to a permanent folder, for example:
   - `C:\tools\meilisearch\meilisearch.exe`

## 2) Start Meilisearch once (manual check)

Run this once to verify it works:

```powershell
cd "C:\tools\meilisearch"
.\meilisearch.exe --http-addr "127.0.0.1:7700" --master-key "local-dev-master-key"
```

Health check:

- Open `http://127.0.0.1:7700/health`
- Expected response:

```json
{"status":"available"}
```

## 3) Configure Laravel environment

In `.env`:

```env
SCOUT_DRIVER=meilisearch
SCOUT_QUEUE=false
MEILISEARCH_HOST=http://127.0.0.1:7700
MEILISEARCH_KEY=local-dev-master-key
```

Notes:

- `SCOUT_QUEUE=false` keeps indexing synchronous (simple local setup).
- If you enable queue later, run a queue worker.

## 4) Rebuild config and index existing documents

From project root:

```powershell
php artisan config:clear
php artisan cache:clear
php artisan scout:flush "App\Models\Document"
php artisan scout:import "App\Models\Document"
php artisan scout:sync-index-settings
```

## 5) Quick verification

Use Tinker:

```powershell
php artisan tinker
```

```php
App\Models\Document::search('fil2')->take(5)->get();
```

If this returns records, Meilisearch is wired correctly.

Employee (201 Files) check:

```php
App\Models\Employee::search('Dela')->take(5)->get();
```

If this returns records, the 201 Files search index is ready.

## 5.1) Re-index employees for 201 Files

Run this after enabling Meilisearch or after large employee imports:

```powershell
php artisan scout:flush "App\Models\Employee"
php artisan scout:import "App\Models\Employee"
php artisan scout:sync-index-settings
```

## UI behavior to expect (important)

Seeing results only after a very short pause is normal and recommended.

- The 201 search input uses a debounce (~100ms).
- Requests start only when query length is at least 2.
- Older in-flight requests are canceled when you keep typing.

This is standard "search-as-you-type" behavior and prevents flooding the server with one request per keystroke.

## 6) No-terminal startup options (run without opening PowerShell manually)

Yes, this is possible.

### Option A: Windows Task Scheduler (recommended)

1. Open **Task Scheduler**.
2. Create a new task named `Meilisearch`.
3. Trigger: `At log on` (or `At startup`).
4. Action: `Start a program`.
   - Program/script: `C:\tools\meilisearch\meilisearch.exe`
   - Arguments: `--http-addr 127.0.0.1:7700 --master-key local-dev-master-key`
   - Start in: `C:\tools\meilisearch`
5. Save, then run the task once to test.

This starts Meilisearch in background automatically.

### Option B: Run as a Windows Service (NSSM)

Use NSSM to register `meilisearch.exe` as a service if you want stronger service controls (auto-restart, service management).

### Option C: Docker Desktop auto-start

If you already use Docker, create a container with restart policy so it starts with Docker Desktop.

## Troubleshooting

### Search still slow or unchanged

- Confirm `.env` has `SCOUT_DRIVER=meilisearch`.
- Run `php artisan config:clear`.
- Ensure Meilisearch health endpoint returns `available`.
- Re-import index with `scout:flush` + `scout:import`.

### Search returns empty results

- Remember: suggestions start at 2 characters minimum.
- Ensure your documents are active and indexed.

### Port conflict on 7700

- Start Meilisearch on another port, e.g. `7701`.
- Update `MEILISEARCH_HOST` accordingly.

### No data after adding new documents

- If `SCOUT_QUEUE=true`, make sure queue worker is running.
- For local simplicity, keep `SCOUT_QUEUE=false`.

## Security reminder

- Do not expose Meilisearch publicly without network restrictions.
- Use a real key for shared/staging/production environments.
