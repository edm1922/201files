<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Truncate tables in correct order to avoid FK issues
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('employees')->truncate();
        DB::table('folders')->truncate();
        DB::table('folder_locations')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ── Administrative Users ──
        $this->call(UserSeeder::class);

        // ── Departments (cooperative-internal) ──
        $departments = [
            ['name' => 'Finance', 'code' => 'FIN', 'description' => 'Financial and payroll documents'],
            ['name' => 'Accounting', 'code' => 'ACCT', 'description' => 'Accounting records and reports'],
            ['name' => 'CDA', 'code' => 'CDA', 'description' => 'Cooperative Development Authority documents'],
            ['name' => 'Braveheart', 'code' => 'BH', 'description' => 'Braveheart division documents'],
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
