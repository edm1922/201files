<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function deployments(): HasMany
    {
        return $this->hasMany(Deployment::class);
    }

    /**
     * Get employees currently deployed to this company.
     */
    public function activeEmployees()
    {
        return $this->hasManyThrough(
            Employee::class,
            Deployment::class,
            'company_id',
            'id',
            'id',
            'employee_id'
        )->where('deployments.is_current', true);
    }
}
