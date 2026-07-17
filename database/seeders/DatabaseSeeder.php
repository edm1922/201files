<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
        DB::table('hiring_events')->truncate();
        DB::table('document_expiry_notifications')->truncate();
        DB::table('department_user_access')->truncate();
        DB::table('audit_logs')->truncate();
        DB::table('documents')->truncate();
        DB::table('document_folders')->truncate();
        DB::table('document_types')->truncate();
        DB::table('employees')->truncate();
        DB::table('folders')->truncate();
        DB::table('folder_locations')->truncate();
        DB::table('company_folder_sequences')->truncate();
        DB::table('departments')->truncate();
        DB::table('users')->truncate();
        DB::table('bank_types')->truncate();
        DB::table('document_locations')->truncate();
        DB::table('companies')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

        $this->call(CompanySeeder::class);
        $this->call(UserSeeder::class);
        $this->call(DepartmentSeeder::class);
        $this->call(DocumentTypeSeeder::class);
        $this->call(DocumentLocationSeeder::class);
        $this->call(BankTypeSeeder::class);
        $this->call(FolderLocationSeeder::class);
        $this->call(FolderSeeder::class);
        $this->call(EmployeeSeeder::class);
        $this->call(DocumentFolderSeeder::class);
        $this->call(DocumentSeeder::class);
        $this->call(DepartmentUserAccessSeeder::class);
        $this->call(HiringEventSeeder::class);
    }
}
