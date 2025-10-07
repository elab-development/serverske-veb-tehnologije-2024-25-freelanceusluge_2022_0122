<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    public function run(): void
    {
        // Profili za sve provajdere koji ih nemaju
        $providers = User::where('role', 'provider')->get();
        foreach ($providers as $user) {
            if (!$user->profile) {
                Profile::factory()->create([
                    'user_id' => $user->id,
                ]);
            }
        }

        // Dodatno par profila ( za test)
        Profile::factory()->count(2)->create();
    }
}
