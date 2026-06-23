<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Human Resources', 'code' => 'HR', 'description' => 'Human resource documents and records', 'is_active' => true],
            ['name' => 'Finance', 'code' => 'FIN', 'description' => 'Financial and payroll documents', 'is_active' => true],
            ['name' => 'Accounting', 'code' => 'ACCT', 'description' => 'Accounting records and reports', 'is_active' => true],
            ['name' => 'CDA', 'code' => 'CDA', 'description' => 'Cooperative Development Authority documents', 'is_active' => true],
            ['name' => 'Braveheart', 'code' => 'BH', 'description' => 'Braveheart division documents', 'is_active' => true],
            ['name' => 'Legal', 'code' => 'LEGAL', 'description' => 'Legal and compliance documents', 'is_active' => true],
            ['name' => 'IT', 'code' => 'IT', 'description' => 'IT and systems documentation', 'is_active' => true],
            ['name' => 'Administrative', 'code' => 'ADMIN', 'description' => 'General administrative records', 'is_active' => true],
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }
    }
}
