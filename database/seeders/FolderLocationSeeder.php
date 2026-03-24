<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FolderLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('folder_locations')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $locations = [
            ['id' => 1, 'row_name' => 'A', 'column_code' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'row_name' => 'A', 'column_code' => '2', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'row_name' => 'B', 'column_code' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'row_name' => 'B', 'column_code' => '2', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('folder_locations')->insert($locations);
    }
}
