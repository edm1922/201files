<?php

namespace Database\Seeders;

use App\Models\Cabinet;
use App\Models\Company;
use App\Models\Department;
use App\Models\DocumentType;
use App\Models\Rack;
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
            Department::create($dept);
        }

        // ── Document Types ──
        $finance = Department::where('name', 'Finance')->first();

        $documentTypes = [
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

        // ── Cabinets & Racks ──
        $cabinetData = ['Cabinet 1', 'Cabinet 2', 'Cabinet 3'];
        $rackCodes   = ['A1', 'A2', 'A3', 'A4', 'A5', 'B1', 'B2', 'B3', 'B4', 'B5'];

        foreach ($cabinetData as $cabinetName) {
            $cabinet = Cabinet::create(['name' => $cabinetName]);

            foreach ($rackCodes as $rackCode) {
                Rack::create([
                    'cabinet_id' => $cabinet->id,
                    'rack_code'  => $rackCode,
                ]);
            }
        }

        // ── Test Employees ──
        $this->call(EmployeeSeeder::class);
    }
}
