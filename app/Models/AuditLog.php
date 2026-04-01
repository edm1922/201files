<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'target_name',
        'description',
        'changes',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent loggable model (Employee, Document, etc.).
     */
    public function model()
    {
        return $this->morphTo()->withTrashed();
    }

    /**
     * Helper to get the name of the target entity.
     */
    public function getTargetNameAttribute()
    {
        // Priority 1: Use the stored target_name if it exists (handles deletions)
        if (!empty($this->attributes['target_name'])) {
            return $this->attributes['target_name'];
        }

        // Priority 2: Try to resolve from the relationship (fallback for legacy logs)
        if (!$this->model) {
            return '—';
        }

        return match ($this->model_type) {
            'App\Models\Employee' => $this->model->full_name,
            'App\Models\User' => $this->model->username ?? $this->model->name,
            'App\Models\Company' => $this->model->name,
            'App\Models\Department' => $this->model->name,
            'App\Models\Document' => $this->model->original_filename,
            default => 'ID: ' . $this->model_id
        };
    }

    /**
     * Helper to get a description without the redundant target name.
     */
    public function getCleanDescriptionAttribute()
    {
        $target = $this->target_name;
        if ($target === '—') {
            return $this->description;
        }

        $desc = $this->description;
        
        // Remove ": Name" or " Name" 
        $desc = str_replace(": " . $target, "", $desc);
        $desc = str_replace($target, "", $desc);
        
        // Clean up any double spaces or trailing punctuation
        $desc = preg_replace('/\s+/', ' ', $desc);
        $desc = rtrim(trim($desc), ':');
        
        return $desc ?: $this->description;
    }
}
