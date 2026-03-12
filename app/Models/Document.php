<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Department; // Added for the new relationship

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'department_id',
        'document_type_id',
        'physical_location_id',
        'uploaded_by',
        'file_path',
        'original_filename',
        'system_filename',
        'page_count',
        'file_size_bytes',
        'mime_type',
        'status',
        'date_received',
        'expiry_date',
        'ocr_text',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'date_received' => 'date',
            'expiry_date' => 'date',
            'metadata' => 'array',
        ];
    }

    /**
     * Check if document is expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Check if document is expiring within given days.
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expiry_date
            && $this->expiry_date->isFuture()
            && $this->expiry_date->diffInDays(now()) <= $days;
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function physicalLocation(): BelongsTo
    {
        return $this->belongsTo(PhysicalLocation::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}
