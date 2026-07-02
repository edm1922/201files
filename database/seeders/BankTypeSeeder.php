<?php

namespace Database\Seeders;

use App\Models\BankType;
use Illuminate\Database\Seeder;

class BankTypeSeeder extends Seeder
{
    public function run(): void
    {
        BankType::create([
            'id' => 1,
            'name' => 'AUB',
            'is_active' => true,
        ]);
    }
}
