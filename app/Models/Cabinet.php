<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Cabinet extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function racks(): HasMany
    {
        return $this->hasMany(Rack::class);
    }

    public function slots(): HasManyThrough
    {
        return $this->hasManyThrough(Slot::class, Rack::class);
    }
}
