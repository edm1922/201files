<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks to allow truncation
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('employees')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $employees = [
           
        ];

        foreach ($employees as $data) {
            DB::table('employees')->insert([
                'id' => $data[0],
                'system_id' => $data[1],
                'barcode_id' => $data[2],
                'first_name' => $data[3],
                'middle_name' => $data[4],
                'last_name' => $data[5],
                'suffix' => $data[6],
                'date_hired' => $data[7],
                'status' => $data[8],
                'archive_date' => $data[9],
                'company_id' => $data[10],
                'folder_id' => $data[11],
                'created_at' => $data[12],
                'updated_at' => $data[13],
                'deleted_at' => $data[14],
                'folder_location_id' => $data[15],
            ]);
        }
    }
}
