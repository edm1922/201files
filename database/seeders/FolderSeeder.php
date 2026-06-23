<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FolderSeeder extends Seeder
{
    public function run(): void
    {
        $now = '2026-03-16 08:33:00';
        $company = \App\Models\Company::find(1);

        if ($company) {
            $folders = [];
            $prefix = 'CSC-' . strtoupper($company->code) . '-';
            $count = 2505;

            for ($i = 1; $i <= $count; $i++) {
                $folders[] = [
                    'company_id' => $company->id,
                    'sequence_number' => $i,
                    'folder_code' => $prefix . str_pad($i, 4, '0', STR_PAD_LEFT),
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
