<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            ['name' => 'Company A', 'code' => 'COA', 'is_active' => true],
            ['name' => 'Company B', 'code' => 'COB', 'is_active' => true],
        ];

        foreach ($companies as $company) {
            Company::create($company);
        }
    }
}
