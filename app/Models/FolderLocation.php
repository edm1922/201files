<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FolderLocation extends Model
{
    protected $fillable = [
        'row_name',
        'max_capacity',
    ];

    /**
     * Helper to convert alphabetical row name (A, B, AA...) to numeric index (1-based).
     */
    public function getRowIndex(): int
    {
        $name = strtoupper($this->row_name);
        $length = strlen($name);
        $index = 0;
        for ($i = 0; $i < $length; $i++) {
            // Check if char is alpha
            if (ctype_alpha($name[$i])) {
                $index *= 26;
                $index += ord($name[$i]) - 64;
            }
        }
        return $index;
    }

    /**
     * Get the employee number range for this row, e.g. "1 - 500"
     */
    public function getRangeAttribute(): string
    {
        $idx = $this->getRowIndex();
        if ($idx <= 0) return '—';

        $capacity = $this->max_capacity ?? 500;
        $start = ($idx - 1) * $capacity + 1;
        $end = $idx * $capacity;

        return number_format($start) . ' - ' . number_format($end);
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
     * Scope a query to only include available folder locations.
     */
    public function scopeAvailable($query)
    {
        return $query->whereHas('employees', null, '<', \DB::raw('max_capacity'));
    }

    /**
     * Full location path, e.g. "Row A (1-500)"
     */
    public function getFullLocationAttribute(): string
    {
        return 'Row ' . $this->row_name . ' (' . $this->range . ')';
    }

    /**
     * Display name, e.g. "Row A (1-500)"
     */
    public function getDisplayNameAttribute(): string
    {
        return 'Row ' . $this->row_name . ' (' . $this->range . ')';
    }
}
