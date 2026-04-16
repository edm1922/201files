<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends Model
{
    protected $fillable = [
        'name',
        'code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get employees assigned to this company.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function folderLocations(): HasMany
    {
        return $this->hasMany(FolderLocation::class);
    }

    public function folders(): HasMany
    {
        return $this->hasMany(Folder::class);
    }

    public function folderSequence(): HasOne
    {
        return $this->hasOne(CompanyFolderSequence::class);
    }
}
