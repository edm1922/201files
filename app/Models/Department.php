<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = [
        'name',
        'code',
        'folder_code',
        'description',
        'is_active',
        'slot_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function documentTypes(): HasMany
    {
        return $this->hasMany(DocumentType::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(Slot::class);
    }
}
