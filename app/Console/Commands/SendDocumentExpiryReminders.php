<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\DocumentExpiryNotification;
use App\Models\User;
use App\Notifications\DocumentExpiryAlertNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class SendDocumentExpiryReminders extends Command
{
    protected $signature = 'documents:send-expiry-reminders';

    protected $description = 'Send in-app reminders for expiring and expired department documents';

    public function handle(): int
    {
        $today = now()->startOfDay();
        $sentCount = 0;

        $reminderDays = collect((array) config('document_expiry.reminder_days', [30, 14, 7, 1]))
            ->map(fn ($day) => (int) $day)
            ->filter(fn (int $day) => $day > 0)
            ->unique()
            ->sortDesc()
            ->values();

        if ($reminderDays->isEmpty()) {
            $this->warn('No reminder days configured.');
        }

        foreach ($reminderDays as $index => $daysBeforeExpiry) {
            $nextLowerThreshold = (int) ($reminderDays->get($index + 1, 0));
            $upperDate = $today->copy()->addDays($daysBeforeExpiry)->toDateString();
            $lowerDate = $today->copy()->addDays($nextLowerThreshold)->toDateString();

            $documents = Document::query()
                ->with(['department.authorizedUsers:id', 'department:id,name'])
                ->where('status', 'active')
                ->whereDate('expiry_date', '>', $lowerDate)
                ->whereDate('expiry_date', '<=', $upperDate)
                ->get();

            foreach ($documents as $document) {
                $recipients = $this->resolveRecipients($document);

                foreach ($recipients as $user) {
                    if ($this->wasAlreadySent($document->id, $user->id, $daysBeforeExpiry, false, $document->expiry_date?->toDateString())) {
                        continue;
                    }

                    $user->notify(new DocumentExpiryAlertNotification($document, $daysBeforeExpiry));

                    DocumentExpiryNotification::create([
                        'document_id' => $document->id,
                        'user_id' => $user->id,
                        'days_before_expiry' => $daysBeforeExpiry,
                        'is_expired_alert' => false,
                        'expiry_date' => $document->expiry_date,
                        'sent_at' => now(),
                    ]);

                    $sentCount++;
                }
            }
        }

        if ((bool) config('document_expiry.send_expired_alert', true)) {
            $expiredDocuments = Document::query()
                ->with(['department.authorizedUsers:id', 'department:id,name'])
                ->where('status', 'active')
                ->whereDate('expiry_date', '<=', $today->toDateString())
                ->get();

            foreach ($expiredDocuments as $document) {
                $recipients = $this->resolveRecipients($document);

                foreach ($recipients as $user) {
                    if ($this->wasAlreadySent($document->id, $user->id, null, true, $document->expiry_date?->toDateString())) {
                        continue;
                    }

                    $user->notify(new DocumentExpiryAlertNotification($document, null));

                    DocumentExpiryNotification::create([
                        'document_id' => $document->id,
                        'user_id' => $user->id,
                        'days_before_expiry' => null,
                        'is_expired_alert' => true,
                        'expiry_date' => $document->expiry_date,
                        'sent_at' => now(),
                    ]);

                    $sentCount++;
                }
            }
        }

        $this->info("Document expiry notifications sent: {$sentCount}");

        return self::SUCCESS;
    }

    private function resolveRecipients(Document $document): Collection
    {
        $departmentUsers = $document->department?->authorizedUsers ?? collect();
        $adminUsers = User::query()->where('role', 'admin')->get(['id']);

        return $departmentUsers
            ->merge($adminUsers)
            ->unique('id')
            ->values();
    }

    private function wasAlreadySent(int $documentId, int $userId, ?int $daysBeforeExpiry, bool $isExpiredAlert, ?string $expiryDate): bool
    {
        if ($expiryDate === null) {
            return true;
        }

        return DocumentExpiryNotification::query()
            ->where('document_id', $documentId)
            ->where('user_id', $userId)
            ->where('expiry_date', $expiryDate)
            ->where('is_expired_alert', $isExpiredAlert)
            ->when(
                $daysBeforeExpiry === null,
                fn ($query) => $query->whereNull('days_before_expiry'),
                fn ($query) => $query->where('days_before_expiry', $daysBeforeExpiry)
            )
            ->exists();
    }
}
