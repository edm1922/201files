<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentUserAccessSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $encoder = User::where('role', 'encoder')->first();

        if ($admin) {
            $departmentIds = Department::pluck('id')->toArray();
            $adminAccess = [];
            foreach ($departmentIds as $deptId) {
                $adminAccess[] = [
                    'user_id' => $admin->id,
                    'department_id' => $deptId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('department_user_access')->insert($adminAccess);
        }

        if ($encoder) {
            $encoderDepts = Department::whereIn('code', ['HR', 'FIN', 'ACCT'])->pluck('id')->toArray();
            $encoderAccess = [];
            foreach ($encoderDepts as $deptId) {
                $encoderAccess[] = [
                    'user_id' => $encoder->id,
                    'department_id' => $deptId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('department_user_access')->insert($encoderAccess);
        }
    }
}
