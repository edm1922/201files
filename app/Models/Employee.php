<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

/**
 * @mixin Builder
 */
class Employee extends Model
{
    use HasFactory, SoftDeletes;
    use Searchable;

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
        'folder_id',
        'folder_location_id',
        'archive_date',
        'atm_status',
        'bank_type_id',
    ];

    protected function casts(): array
    {
        return [
            'date_hired' => 'date',
            'archive_date' => 'date',
        ];
    }

    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = strtoupper($value);
    }

    public function setMiddleNameAttribute($value)
    {
        $this->attributes['middle_name'] = strtoupper($value);
    }

    public function setLastNameAttribute($value)
    {
        $this->attributes['last_name'] = strtoupper($value);
    }

    /**
     * Full name accessor: "LAST_NAME, FIRST_NAME MIDDLE_NAME SUFFIX"
     */
    public function getFullNameAttribute(): string
    {
        $name = strtoupper($this->last_name).', '.$this->first_name;

        if ($this->middle_name) {
            $name .= ' '.$this->middle_name;
        }

        if ($this->suffix) {
            $name .= ' '.$this->suffix;
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

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function folderLocation(): BelongsTo
    {
        return $this->belongsTo(FolderLocation::class);
    }

    public function bankType(): BelongsTo
    {
        return $this->belongsTo(BankType::class);
    }

    public function searchableAs(): string
    {
        return 'employees';
    }

    public function shouldBeSearchable(): bool
    {
        return $this->status !== 'resigned' && $this->deleted_at === null;
    }

    public function toSearchableArray(): array
    {
        $this->loadMissing([
            'folder:id,folder_code',
        ]);

        return [
            'id' => (int) $this->id,
            'first_name' => (string) ($this->first_name ?? ''),
            'middle_name' => (string) ($this->middle_name ?? ''),
            'last_name' => (string) ($this->last_name ?? ''),
            'full_name' => trim((string) (($this->last_name ?? '').' '.($this->first_name ?? '').' '.($this->middle_name ?? ''))),
            'system_id' => (string) ($this->system_id ?? ''),
            'barcode_id' => (string) ($this->barcode_id ?? ''),
            'folder_code' => (string) ($this->folder?->folder_code ?? ''),
            'status' => (string) ($this->status ?? ''),
            'updated_at' => optional($this->updated_at)?->timestamp,
        ];
    }
}
