<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FolderLocationSeeder extends Seeder
{
    public function run(): void
    {
        $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];
        $locations = [];
        foreach ($rows as $index => $row) {
            $rangeStart = ($index * 500) + 1;
            $rangeEnd = ($index + 1) * 500;

            $locations[] = [
                'id' => $index + 1,
                'company_id' => 1,
                'row_name' => $row,
                'range_start' => $rangeStart,
                'range_end' => $rangeEnd,
                'max_capacity' => 500,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('folder_locations')->insert($locations);
    }
}
