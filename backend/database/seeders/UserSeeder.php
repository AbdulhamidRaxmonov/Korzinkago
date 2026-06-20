<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Test mijoz
        User::updateOrCreate(['phone' => '998901234567'], [
            'name' => 'Test Mijoz',
            'role' => 'user',
            'phone_verified_at' => now(),
        ]);

        // Test kuryer
        User::updateOrCreate(['phone' => '998907654321'], [
            'name' => 'Test Kuryer',
            'role' => 'courier',
            'vehicle_type' => 'bike',
            'is_online' => true,
            'phone_verified_at' => now(),
        ]);

        // Admin
        User::updateOrCreate(['phone' => '998900000000'], [
            'name' => 'Administrator',
            'role' => 'admin',
            'password' => Hash::make('admin123'),
            'phone_verified_at' => now(),
        ]);
    }
}
