<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
        DB::table('company_folder_sequences')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $now = '2026-03-16 08:33:00';
        $companies = \App\Models\Company::all();
        
        foreach ($companies as $company) {
            $folders = [];
            $prefix = 'CSC-' . strtoupper($company->code) . '-';
            $count = ($company->id == 1) ? 2151 : 50; // More for General Tuna, less for others
            
            for ($i = 1; $i <= $count; $i++) {
                $folders[] = [
                    'company_id' => $company->id,
                    'sequence_number' => $i,
                    'folder_code' => $prefix.str_pad($i, 4, '0', STR_PAD_LEFT),
                    'is_available' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach (array_chunk($folders, 200) as $chunk) {
                DB::table('folders')->insert($chunk);
            }

            DB::table('company_folder_sequences')->insert([
                'company_id' => $company->id,
                'next_number' => $count + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
