<?php

namespace App\Providers;

use App\Models\Document;
use App\Policies\DepartmentDocumentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Document::class => DepartmentDocumentPolicy::class,
    ];
}
