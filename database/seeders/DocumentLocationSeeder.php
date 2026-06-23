<?php

namespace Database\Seeders;

use App\Models\DocumentLocation;
use Illuminate\Database\Seeder;

class DocumentLocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            ['name' => 'Main Cabinet A - Top Shelf'],
            ['name' => 'Main Cabinet A - Middle Shelf'],
            ['name' => 'Main Cabinet A - Bottom Shelf'],
            ['name' => 'Main Cabinet B - Top Shelf'],
            ['name' => 'Main Cabinet B - Middle Shelf'],
            ['name' => 'Main Cabinet B - Bottom Shelf'],
            ['name' => 'Archive Cabinet 1'],
            ['name' => 'Archive Cabinet 2'],
            ['name' => 'Digital Storage - HR'],
            ['name' => 'Digital Storage - Finance'],
            ['name' => 'Digital Storage - Legal'],
        ];

        foreach ($locations as $loc) {
            DocumentLocation::create($loc);
        }
    }
}
