<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\DocumentType;
use App\Models\PhysicalLocation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin User ──
        User::create([
            'name' => 'Admin',
            'email' => 'admin@csc.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // ── Encoder User (for testing) ──
        User::create([
            'name' => 'Encoder',
            'email' => 'encoder@csc.com',
            'password' => Hash::make('password'),
            'role' => 'encoder',
            'email_verified_at' => now(),
        ]);

        // ── Viewer User (for testing) ──
        User::create([
            'name' => 'Viewer',
            'email' => 'viewer@csc.com',
            'password' => Hash::make('password'),
            'role' => 'viewer',
            'email_verified_at' => now(),
        ]);

        // ── Departments (cooperative-internal) ──
        $departments = [
            ['name' => 'Finance', 'code' => 'FIN', 'folder_code' => 'CSC-FIN-0000', 'description' => 'Financial and payroll documents'],
            ['name' => 'Accounting', 'code' => 'ACCT', 'folder_code' => 'CSC-ACCT-0000', 'description' => 'Accounting records and reports'],
            ['name' => 'CDA', 'code' => 'CDA', 'folder_code' => 'CSC-CDA-0000', 'description' => 'Cooperative Development Authority documents'],
            ['name' => 'Braveheart', 'code' => 'BH', 'folder_code' => 'CSC-BH-0000', 'description' => 'Braveheart division documents'],
        ];
        foreach ($departments as $dept) {
            Department::create($dept);
        }

        // ── Document Types ──
        $hr = Department::where('name', 'Human Resource')->first();
        $finance = Department::where('name', 'Finance')->first();

        $documentTypes = [
            // Finance Documents
            ['department_id' => $finance?->id, 'name' => 'Business Permit', 'code' => 'BIZPERMIT', 'has_expiry' => true, 'max_pages' => 2],
        ];

        foreach ($documentTypes as $type) {
            DocumentType::create($type);
        }

        // ── Sample Companies ──
        $companies = [
            ['name' => 'Sample Company A', 'code' => 'COMP-A'],
            ['name' => 'Sample Company B', 'code' => 'COMP-B'],
        ];
        foreach ($companies as $company) {
            Company::create($company);
        }

        // ── Physical Locations (Cabinets & Racks) ──
        $cabinets = ['Cabinet 1', 'Cabinet 2', 'Cabinet 3'];
        $racks = ['A1', 'A2', 'A3', 'A4', 'A5', 'B1', 'B2', 'B3', 'B4', 'B5'];

        foreach ($cabinets as $cabinet) {
            foreach ($racks as $rack) {
                PhysicalLocation::create([
                    'cabinet_id' => $cabinet,
                    'rack_id' => $rack,
                ]);
            }
        }

        // ── Test Employees ──
        $this->call(EmployeeSeeder::class);
    }
}
