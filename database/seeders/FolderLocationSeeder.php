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

        $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];
        $locations = [];
        foreach ($rows as $index => $row) {
            $locations[] = [
                'id' => $index + 1,
                'row_name' => $row,
                'max_capacity' => 500,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        DB::table('folder_locations')->insert($locations);
    }
}
