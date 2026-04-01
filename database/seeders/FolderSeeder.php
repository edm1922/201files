<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FolderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks to allow truncation
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('folders')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $now = '2026-03-16 08:33:00';
        $folders = [];
        for ($i = 1; $i <= 1830; $i++) {
            $folders[] = [
                'id' => $i,
                'folder_code' => 'CSC-HR-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'is_available' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }


        foreach (array_chunk($folders, 200) as $chunk) {
            DB::table('folders')->insert($chunk);
        }
    }
}
