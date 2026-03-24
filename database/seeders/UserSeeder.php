<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks to allow truncation
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ── Admin User ──
        User::factory()->admin()->create([
            'first_name' => 'System',
            'last_name' => 'Admin',
            'username' => 'admin',
            'password' => Hash::make('admincsc'),
        ]);

        // ── Encoder User ──
        User::factory()->encoder()->create([
            'first_name' => 'Test',
            'last_name' => 'Encoder',
            'username' => 'encode',
            'password' => Hash::make('encodercsc'),
        ]);

        // ── Viewer User ──
        User::factory()->viewer()->create([
            'first_name' => 'Test',
            'last_name' => 'Viewer',
            'username' => 'viewer',
            'password' => Hash::make('viewercsc'),
        ]);
    }
}
