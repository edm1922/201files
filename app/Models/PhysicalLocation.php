<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhysicalLocation extends Model
{
    protected $fillable = [
        'cabinet_id',
        'rack_id',
        'label',
    ];

    /**
     * Display label: "Cabinet 1 - Rack A1"
     */
    public function getDisplayNameAttribute(): string
    {
        $name = $this->cabinet_id . ' - ' . $this->rack_id;
        return $this->label ? $name . ' (' . $this->label . ')' : $name;
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
