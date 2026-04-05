# Document Expiry Reminders (Windows + Office PC)

This guide explains how expiry notifications work in this project and how to run them reliably on a Windows office PC.

## What is implemented

- Reminder command: `php artisan documents:send-expiry-reminders`
- Scheduler frequency: every 15 minutes
- Delivery channel: in-app (database notifications)
- Dedupe log: `document_expiry_notifications` table
- Expired alerts: sent once for active docs where `expiry_date <= today`

## Reminder windows (catch-up aware)

Reminders are window-based and sent once per window:

- 30-day reminder window: days-left `15-30`
- 14-day reminder window: days-left `8-14`
- 7-day reminder window: days-left `2-7`
- 1-day reminder window: days-left `1`

If the PC/server is OFF during a day, reminders are sent on next uptime (next successful scheduler run), based on the current days-left window.

## One-time setup

From project root:

```powershell
php artisan migrate
```

This creates:

- `notifications` (Laravel in-app notifications)
- `document_expiry_notifications` (dedupe/send-history log)

## Windows Task Scheduler setup (required on office PC)

Laravel schedules do not run by themselves. You must trigger `schedule:run` using Windows Task Scheduler.

### Recommended task: run every 5-15 minutes

1. Open **Task Scheduler**.
2. Create task name: `CSC-DMS Laravel Scheduler`.
3. Trigger: `Daily` and repeat task every `15 minutes` for `Indefinitely`.
4. Action: `Start a program`.
   - Program/script: `C:\xampp\php\php.exe`
   - Add arguments: `artisan schedule:run`
   - Start in: `C:\xampp\htdocs\CSC-Document-Management-System`
5. In Conditions/Settings, allow run on AC power only if desired.
6. Save and run once manually to verify.

### Optional trigger: at startup/logon

Add an additional trigger (`At startup` or `At log on`) so reminders resume quickly after reboot.

## Manual verification

```powershell
php artisan schedule:list
php artisan documents:send-expiry-reminders
```

Expected:

- Scheduler shows `documents:send-expiry-reminders` every 15 minutes.
- Command output shows `Document expiry notifications sent: <n>`.

## UI verification

- Open Notifications page: `/notifications`
- Bell icon badge in top navbar should show unread count.
- Click `Mark as read` or `Mark all as read` to update notification state.

## Troubleshooting

### No reminders received

- Confirm Task Scheduler task is enabled and has successful run history.
- Run command manually: `php artisan documents:send-expiry-reminders`.
- Ensure target documents are `status = active` and have `expiry_date` set.

### Duplicate reminders

- Check `document_expiry_notifications` migration is applied.
- Do not truncate dedupe table in production.

### PC is OFF for several days

- This is supported.
- On next uptime, reminders are evaluated by current window and expired alerts are sent once.
