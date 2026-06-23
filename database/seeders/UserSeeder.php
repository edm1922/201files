<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'first_name' => 'Jemar',
            'last_name' => 'Barrera',
            'username' => 'admin',
            'password' => Hash::make('barreracsc'),
        ]);
        User::factory()->encoder()->create([
            'first_name' => 'Christine Marie',
            'last_name' => 'Bernales',
            'username' => 'Bernales',
            'password' => Hash::make('bernalescsc'),
        ]);
    }
}
