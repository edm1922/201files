<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Rack extends Model
{
    protected $fillable = [
        'cabinet_id',
        'rack_code',
    ];

    public function cabinet(): BelongsTo
    {
        return $this->belongsTo(Cabinet::class);
    }

    public function slots(): HasMany
    {
        return $this->hasMany(Slot::class);
    }

    /**
     * Display label: "Cabinet 1 › A1"
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->cabinet->name . ' › ' . $this->rack_code;
    }
}
