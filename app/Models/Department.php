<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = [
        'name',
        'code',
        'folder_code',
        'description',
        'is_active',
        'folder_location_id',
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

    public function documentFolders(): HasMany
    {
        return $this->hasMany(DocumentFolder::class);
    }

    public function authorizedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'department_user_access')->withTimestamps();
    }

    public function folderLocation(): BelongsTo
    {
        return $this->belongsTo(FolderLocation::class);
    }
}
