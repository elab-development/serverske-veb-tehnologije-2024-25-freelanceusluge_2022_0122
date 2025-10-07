<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
      
        $client = User::updateOrCreate(
            ['email' => 'puacauros1@gmail.com'],
            [
                'name' => 'UrosPuaca',
                'password' => Hash::make('password'),
                'role' => 'client',
                'email_verified_at' => now(),
            ]
        );

        $provider = User::updateOrCreate(
            ['email' => 'radomirovicj03@gmail.com'],
            [
                'name' => 'radomirovicjovan',
                'password' => Hash::make('password'),
                'role' => 'provider',
                'email_verified_at' => now(),
            ]
        );

        // Još po nekoliko nasumičnih korisnika
        User::factory()->client()->count(3)->create();
        User::factory()->provider()->count(5)->create();
    }
}
