<?php

use App\Models\Department;
use App\Models\DocumentFolder;
use App\Models\User;

it('shows nested folder tree and breadcrumb within selected department', function () {
    $department = Department::create([
        'name' => 'Explorer Dept',
        'code' => 'EXD',
        'folder_code' => 'CSC-EXD-0000',
        'description' => 'Explorer Department',
        'is_active' => true,
    ]);

    $root = DocumentFolder::create([
        'department_id' => $department->id,
        'parent_id' => null,
        'name' => 'Root Policies',
    ]);

    $child = DocumentFolder::create([
        'department_id' => $department->id,
        'parent_id' => $root->id,
        'name' => '2026',
    ]);

    $user = User::factory()->viewer()->create();
    $user->authorizedDepartments()->sync([$department->id]);

    $response = $this->actingAs($user)->get(route('department-documents.index', [
        'department_id' => $department->id,
        'document_folder_id' => $child->id,
    ]));

    $response->assertOk();
    $response->assertSee('Root Policies');
    $response->assertSee('2026');
    $response->assertSee('Current Location');
});

it('hides folder tree entries from unauthorized departments', function () {
    $departmentA = Department::create([
        'name' => 'Allowed Dept',
        'code' => 'ALD',
        'folder_code' => 'CSC-ALD-0000',
        'description' => 'Allowed Department',
        'is_active' => true,
    ]);

    $departmentB = Department::create([
        'name' => 'Hidden Dept',
        'code' => 'HDD',
        'folder_code' => 'CSC-HDD-0000',
        'description' => 'Hidden Department',
        'is_active' => true,
    ]);

    DocumentFolder::create([
        'department_id' => $departmentA->id,
        'parent_id' => null,
        'name' => 'Allowed Folder',
    ]);

    DocumentFolder::create([
        'department_id' => $departmentB->id,
        'parent_id' => null,
        'name' => 'Hidden Folder',
    ]);

    $user = User::factory()->viewer()->create();
    $user->authorizedDepartments()->sync([$departmentA->id]);

    $response = $this->actingAs($user)->get(route('department-documents.index', [
        'department_id' => $departmentA->id,
    ]));

    $response->assertOk();
    $response->assertSee('Allowed Folder');
    $response->assertDontSee('Hidden Folder');
});
