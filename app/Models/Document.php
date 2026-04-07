<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Document extends Model
{
    use Searchable;
    use SoftDeletes;

    protected $fillable = [
        'department_id',
        'document_type_id',
        'folder_location_id',
        'document_location_id',
        'document_folder_id',
        'uploaded_by',
        'file_path',
        'original_filename',
        'system_filename',
        'page_count',
        'file_size_bytes',
        'mime_type',
        'upload_mode',
        'status',
        'date_received',
        'expiry_date',
        'ocr_text',
        'metadata',
        'source_filenames',
    ];

    protected function casts(): array
    {
        return [
            'date_received' => 'date',
            'expiry_date' => 'date',
            'metadata' => 'array',
            'source_filenames' => 'array',
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
        if (!$this->expiry_date) {
            return false;
        }

        $now = now()->startOfDay();
        $target = $this->expiry_date->copy()->startOfDay();

        if ($target->lessThanOrEqualTo($now)) {
            return false;
        }

        return $now->diffInDays($target, false) <= $days;
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function folderLocation(): BelongsTo
    {
        return $this->belongsTo(FolderLocation::class);
    }

    public function documentFolder(): BelongsTo
    {
        return $this->belongsTo(DocumentFolder::class);
    }

    public function documentLocation(): BelongsTo
    {
        return $this->belongsTo(DocumentLocation::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function searchableAs(): string
    {
        return 'documents';
    }

    public function shouldBeSearchable(): bool
    {
        return $this->status === 'active' && $this->deleted_at === null;
    }

    public function toSearchableArray(): array
    {
        $this->loadMissing([
            'department:id,name,code',
            'documentFolder:id,name,folder_code',
        ]);

        return [
            'id' => (int) $this->id,
            'original_filename' => (string) ($this->original_filename ?? ''),
            'department_id' => (int) ($this->department_id ?? 0),
            'department_name' => (string) ($this->department?->name ?? ''),
            'department_code' => (string) ($this->department?->code ?? ''),
            'document_folder_id' => $this->document_folder_id ? (int) $this->document_folder_id : null,
            'folder_name' => (string) ($this->documentFolder?->name ?? ''),
            'folder_code' => (string) ($this->documentFolder?->folder_code ?? ''),
            'status' => (string) ($this->status ?? ''),
            'updated_at' => optional($this->updated_at)?->timestamp,
        ];
    }
}
