<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FolderLocation extends Model
{
    protected $fillable = [
        'row_name',
        'column_code',
        'folder_code',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
        ];
    }

    /**
     * The employee currently occupying this folder location.
     */
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Documents associated with this folder location.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Departments associated with this folder location.
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    /**
     * Scope: only available folder locations.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Full location path, e.g. "A1 › CSC-HR-0042"
     */
    public function getFullLocationAttribute(): string
    {
        return $this->row_name . $this->column_code . ' › ' . $this->folder_code;
    }

    /**
     * Display name, e.g. "A1"
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->row_name . $this->column_code;
    }
}
