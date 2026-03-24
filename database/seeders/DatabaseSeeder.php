<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Administrative Users ──
        $this->call(UserSeeder::class);

        // ── Departments (cooperative-internal) ──
        $departments = [
            ['name' => 'Finance', 'code' => 'FIN', 'folder_code' => 'CSC-FIN-0000', 'description' => 'Financial and payroll documents'],
            ['name' => 'Accounting', 'code' => 'ACCT', 'folder_code' => 'CSC-ACCT-0000', 'description' => 'Accounting records and reports'],
            ['name' => 'CDA', 'code' => 'CDA', 'folder_code' => 'CSC-CDA-0000', 'description' => 'Cooperative Development Authority documents'],
            ['name' => 'Braveheart', 'code' => 'BH', 'folder_code' => 'CSC-BH-0000', 'description' => 'Braveheart division documents'],
        ];
        foreach ($departments as $dept) {
            Department::updateOrCreate(['code' => $dept['code']], $dept);
        }

        // ── Document Types ──
        $finance = Department::where('name', 'Finance')->first();

        $documentTypes = [
            ['department_id' => $finance?->id, 'name' => 'Business Permit', 'code' => 'BIZPERMIT', 'has_expiry' => true, 'max_pages' => 2],
        ];

        foreach ($documentTypes as $type) {
            DocumentType::updateOrCreate(['code' => $type['code']], $type);
        }

        // ── Physical Storage ──
        $this->call(FolderLocationSeeder::class);

        // ── Digital Folders ──
        $this->call(FolderSeeder::class);

        // ── Sample Companies ──
        $this->call(CompanySeeder::class);

        // ── Test Employees ──
        $this->call(EmployeeSeeder::class);
    }
}
