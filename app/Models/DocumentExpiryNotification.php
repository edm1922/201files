<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentExpiryNotification extends Model
{
    protected $fillable = [
        'document_id',
        'user_id',
        'days_before_expiry',
        'is_expired_alert',
        'expiry_date',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'is_expired_alert' => 'boolean',
            'sent_at' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
