<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks to allow truncation if needed
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('companies')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $companies = [
            ['id' => 1, 'name' => 'GENERAL TUNA CORPORATION', 'code' => 'GTC', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'GAISANO', 'code' => 'G-MALL', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => '7-ELEVEN', 'code' => '7-11', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'TRANS PACIFIC JOURNEY FISHING CORPORATION', 'code' => 'TPJ', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'name' => 'ALL FORWARD WAREHOUSING INC.', 'code' => 'AFW', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'name' => 'ASIA UNITED BANK', 'code' => 'AUB', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'name' => 'BRAVE HEART CONSUMER GOODS TRADING', 'code' => 'BHCGT', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'name' => 'CENTRO EXPRESS ENTERPRISES', 'code' => 'CEE', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'name' => 'CENTRO SERVICES COOPERATIVE', 'code' => 'CSC', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 10, 'name' => 'CENTURY PACIFIC FOOD, INC', 'code' => 'CPFI', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 11, 'name' => 'DELTA POWER TRADE', 'code' => 'DPT', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 12, 'name' => 'LIBCAP SUPER EXPRESS CORPORATION', 'code' => 'LIBCAP', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 13, 'name' => 'MALALAG BAY AQUACULTURE &amp; PROCESSING CORPORATION', 'code' => 'MBAPC', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 14, 'name' => 'MANDAUE FOAM INDUSTRIES, INC', 'code' => 'MANDAUE', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 15, 'name' => 'NOVAL DRESSED CHICKEN AND POULTRY FARM', 'code' => 'NOVAL', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 16, 'name' => 'P.G. ANG &amp; SONS, INC.', 'code' => 'PG ANG', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 17, 'name' => 'PHIL-UNION CANNING CORPORATION, INC', 'code' => 'PHIL-UNION', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 18, 'name' => 'PHIL-UNION FROZEN FOODS, INC.', 'code' => 'PUFFI', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 19, 'name' => 'PHILIPPINE CINMIC INDUSTRIAL CORPORATION', 'code' => 'PCIC', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 20, 'name' => 'RDEX FOOD INTERNATIONAL PHILS., INC', 'code' => 'RDEX', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 21, 'name' => 'SIMPLEX INDUSTRIAL CORPORATION', 'code' => 'SIC', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('companies')->insert($companies);
    }
}
