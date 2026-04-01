<?php

namespace Database\Seeders;

use App\Models\BankType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear is handled in DatabaseSeeder globally

        BankType::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'AUB',
                'is_active' => true,
            ]
        );
    }
}
