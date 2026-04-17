<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\DocumentType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Truncate tables in correct order to avoid FK issues
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('employees')->truncate();
        DB::table('folders')->truncate();
        DB::table('folder_locations')->truncate();
        DB::table('company_folder_sequences')->truncate();
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
            ['department_id' => $finance?->id, 'name' => 'Business Permit', 'code' => 'BIZPERMIT', 'has_expiry' => true],
        ];

        foreach ($documentTypes as $type) {
            DocumentType::updateOrCreate(['code' => $type['code']], $type);
        }

        // ── Sample Companies ──
        $this->call(CompanySeeder::class);

        // ── Physical Storage ──
        $this->call(FolderLocationSeeder::class);

        // ── Digital Folders ──
        $this->call(FolderSeeder::class);

        $this->call(BankTypeSeeder::class);

        // ── Test Employees ──
        $this->call(EmployeeSeeder::class);
    }
}
