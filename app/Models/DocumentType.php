<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentType extends Model
{
    protected $fillable = [
        'department_id',
        'name',
        'code',
        'has_expiry',
        'is_required',
        'max_pages',
    ];

    protected function casts(): array
    {
        return [
            'has_expiry' => 'boolean',
            'is_required' => 'boolean',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
