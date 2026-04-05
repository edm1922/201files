<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentExpiryAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Document $document,
        public readonly ?int $daysBeforeExpiry = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = $this->daysBeforeExpiry === null
            ? 'has expired'
            : sprintf('will expire in %d day(s)', $this->daysBeforeExpiry);

        return (new MailMessage)
            ->subject('Document expiry reminder')
            ->line(sprintf('"%s" %s.', $this->document->original_filename, $label));
    }

    public function toArray(object $notifiable): array
    {
        $expiryDate = $this->document->expiry_date;

        return [
            'kind' => 'document_expiry',
            'document_id' => (int) $this->document->id,
            'document_name' => $this->document->original_filename,
            'department_id' => (int) $this->document->department_id,
            'department_name' => $this->document->department?->name,
            'days_before_expiry' => $this->daysBeforeExpiry,
            'is_expired' => $this->daysBeforeExpiry === null,
            'expiry_date' => $expiryDate?->toDateString(),
            'message' => $this->buildMessage(),
            'url' => route('department-documents.index', [
                'department_id' => (int) $this->document->department_id,
                'document_id' => (int) $this->document->id,
            ]),
        ];
    }

    private function buildMessage(): string
    {
        $name = $this->document->original_filename;
        $expiryDate = $this->document->expiry_date?->format('M d, Y') ?? 'N/A';

        if ($this->daysBeforeExpiry === null) {
            return sprintf('"%s" expired on %s.', $name, $expiryDate);
        }

        return sprintf('"%s" will expire on %s (in %d day(s)).', $name, $expiryDate, $this->daysBeforeExpiry);
    }
}
