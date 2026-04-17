<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Folder extends Model
{
    protected $fillable = [
        'company_id',
        'sequence_number',
        'folder_code',
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
