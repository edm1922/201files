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
            'first_name' => 'Admin',
            'last_name' => 'User',
            'username' => 'admin',
            'password' => Hash::make('admin' . config('brand.password_suffix')),
        ]);
    }
}
