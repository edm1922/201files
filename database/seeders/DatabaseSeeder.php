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
            ['name' => 'Human Resource', 'description' => 'HR and employment documents'],
            ['name' => 'Finance', 'description' => 'Financial and payroll documents'],
            ['name' => 'Accounting', 'description' => 'Accounting records and reports'],
            ['name' => 'CDA', 'description' => 'Cooperative Development Authority documents'],
            ['name' => 'Braveheart', 'description' => 'Braveheart division documents'],
        ];
        foreach ($departments as $dept) {
            Department::create($dept);
        }

        // ── Document Types ──
        $hr = Department::where('name', 'Human Resource')->first();
        $finance = Department::where('name', 'Finance')->first();

        $documentTypes = [
            // HR Documents
            ['department_id' => $hr?->id, 'name' => 'SSS E1 Form', 'code' => 'SSS', 'has_expiry' => false, 'is_required' => true, 'max_pages' => 2],
            ['department_id' => $hr?->id, 'name' => 'PhilHealth MDR', 'code' => 'PHIL', 'has_expiry' => false, 'is_required' => true, 'max_pages' => 1],
            ['department_id' => $hr?->id, 'name' => 'Pag-IBIG MDF', 'code' => 'PAGIBIG', 'has_expiry' => false, 'is_required' => true, 'max_pages' => 1],
            ['department_id' => $hr?->id, 'name' => 'NBI Clearance', 'code' => 'NBI', 'has_expiry' => true, 'is_required' => true, 'max_pages' => 1],
            ['department_id' => $hr?->id, 'name' => 'Birth Certificate', 'code' => 'BIRTHCERT', 'has_expiry' => false, 'is_required' => true, 'max_pages' => 2],
            ['department_id' => $hr?->id, 'name' => 'TIN ID / Form 1902', 'code' => 'TIN', 'has_expiry' => false, 'is_required' => true, 'max_pages' => 1],
            ['department_id' => $hr?->id, 'name' => 'HMO Card', 'code' => 'HMO', 'has_expiry' => true, 'is_required' => true, 'max_pages' => 1],
            ['department_id' => $hr?->id, 'name' => 'Resume / CV', 'code' => 'RESUME', 'has_expiry' => false, 'is_required' => false, 'max_pages' => 3],
            ['department_id' => $hr?->id, 'name' => 'Diploma / TOR', 'code' => 'DIPLOMA', 'has_expiry' => false, 'is_required' => false, 'max_pages' => 2],
            ['department_id' => $hr?->id, 'name' => 'Barangay Clearance', 'code' => 'BARANGAY', 'has_expiry' => true, 'is_required' => false, 'max_pages' => 1],
            ['department_id' => $hr?->id, 'name' => 'Police Clearance', 'code' => 'POLICE', 'has_expiry' => true, 'is_required' => false, 'max_pages' => 1],
            ['department_id' => $hr?->id, 'name' => 'Drug Test Result', 'code' => 'DRUGTEST', 'has_expiry' => true, 'is_required' => false, 'max_pages' => 1],
            ['department_id' => $hr?->id, 'name' => 'Medical Certificate', 'code' => 'MEDCERT', 'has_expiry' => true, 'is_required' => false, 'max_pages' => 1],
            ['department_id' => $hr?->id, 'name' => '2x2 ID Photo', 'code' => 'PHOTO', 'has_expiry' => false, 'is_required' => false, 'max_pages' => 1],

            // Finance Documents
            ['department_id' => $finance?->id, 'name' => 'Business Permit', 'code' => 'BIZPERMIT', 'has_expiry' => true, 'is_required' => false, 'max_pages' => 2],
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
    }
}
