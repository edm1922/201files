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
    ];

    protected function casts(): array
    {
        return [];
    }

    /**
     * The employees occupying this folder location.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
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
     * Scope a query to only include available folder locations.
     */
    public function scopeAvailable($query)
    {
        return $query->doesntHave('employees')->doesntHave('departments');
    }

    /**
     * Full location path, e.g. "A1"
     */
    public function getFullLocationAttribute(): string
    {
        return 'Row ' . $this->row_name . ' - Column ' . $this->column_code;
    }

    /**
     * Display name, e.g. "Row A - Column 1"
     */
    public function getDisplayNameAttribute(): string
    {
        return 'Row ' . $this->row_name . ' - Column ' . $this->column_code;
    }
}
