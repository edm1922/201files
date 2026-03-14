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
        'folder_location_id',
        'archive_date',
    ];

    protected function casts(): array
    {
        return [
            'date_hired'   => 'date',
            'archive_date' => 'date',
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
     * Scope: only archived (resigned) employees.
     */
    public function scopeArchived($query)
    {
        return $query->withTrashed()->where('status', 'resigned');
    }

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function folderLocation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FolderLocation::class);
    }
}
