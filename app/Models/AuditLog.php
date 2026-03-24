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
        if (!$this->model) {
            return '—';
        }

        return match ($this->model_type) {
            'App\Models\Employee' => $this->model->full_name,
            'App\Models\User' => $this->model->name,
            'App\Models\Company' => $this->model->name,
            'App\Models\Department' => $this->model->name,
            'App\Models\Document' => $this->model->original_filename,
            default => 'ID: ' . $this->model_id
        };
    }
}
