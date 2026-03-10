<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'system_id',
        'barcode_id',
        'folder_code',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'date_of_birth',
        'status',
        'company_id',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
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


    public function documents()
    {
        return $this->morphMany(Document::class, 'owner');
    }

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
