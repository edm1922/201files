<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Slot extends Model
{
    protected $fillable = [
        'rack_id',
        'folder_code',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
        ];
    }

    public function rack(): BelongsTo
    {
        return $this->belongsTo(Rack::class);
    }

    /**
     * The employee currently occupying this slot (if any).
     */
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Scope: only available slots.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Full location path, e.g. "Cabinet 1 › A1 › CSC-HR-0042"
     */
    public function getFullLocationAttribute(): string
    {
        return $this->rack->display_name . ' › ' . $this->folder_code;
    }
}
