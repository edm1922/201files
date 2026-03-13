<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'system_id',
        'barcode_id',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'date_hired',
        'status',
        'company_id',
        'slot_id',
    ];

    protected function casts(): array
    {
        return [
            'date_hired' => 'date',
        ];
    }

    /**
     * Full name accessor: "LAST_NAME, FIRST_NAME MIDDLE_NAME SUFFIX"
     */
    public function getFullNameAttribute(): string
    {
        $name = strtoupper($this->last_name) . ', ' . $this->first_name;

        if ($this->middle_name) {
            $name .= ' ' . $this->middle_name;
        }

        if ($this->suffix) {
            $name .= ' ' . $this->suffix;
        }

        return $name;
    }

    /**
     * Scope: only archived (soft-deleted resigned) employees.
     */
    public function scopeArchived($query)
    {
        return $query->onlyTrashed()->where('status', 'resigned');
    }

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function slot(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Slot::class);
    }
}
