<?php

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;

it('allows admin to open reports generate page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('reports.generate'))
        ->assertOk();
});

it('blocks viewer from opening reports generate page', function () {
    $viewer = User::factory()->viewer()->create();

    $this->actingAs($viewer)
        ->get(route('reports.generate'))
        ->assertForbidden();
});

it('validates employee export filters and redirects with errors for invalid status', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('reports.export-employees', [
        'status' => 'invalid-status',
    ]));

    $response->assertRedirect();
    $response->assertSessionHasErrors(['status']);
});

it('validates audit export date range and redirects with errors', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('reports.export-audit-logs', [
        'date_from' => '2026-03-30',
        'date_to' => '2026-03-01',
    ]));

    $response->assertRedirect();
    $response->assertSessionHasErrors(['date_to']);
});

it('downloads employee export as csv when filters are valid', function () {
    $admin = User::factory()->admin()->create();

    $company = Company::create([
        'name' => 'Export Company',
        'code' => 'EXP' . random_int(1000, 9999),
        'is_active' => true,
    ]);

    Employee::factory()->create([
        'company_id' => $company->id,
        'status' => 'active',
    ]);

    $response = $this->actingAs($admin)->get(route('reports.export-employees', [
        'company_id' => $company->id,
        'status' => 'active',
    ]));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    $response->assertHeader('Content-Disposition');
});

it('downloads audit log export as csv when date range is valid', function () {
    $admin = User::factory()->admin()->create();

    AuditLog::create([
        'user_id' => $admin->id,
        'action' => 'created',
        'model_type' => null,
        'model_id' => null,
        'description' => 'Created test record',
        'changes' => null,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',
        'created_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get(route('reports.export-audit-logs', [
        'date_from' => now()->subDay()->toDateString(),
        'date_to' => now()->addDay()->toDateString(),
    ]));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    $response->assertHeader('Content-Disposition');
});
