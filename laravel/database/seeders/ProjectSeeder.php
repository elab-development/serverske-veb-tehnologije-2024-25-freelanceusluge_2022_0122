<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        // Par projekata za konkretnog klijenta iz UserSeeder-a
        $client = User::where('email', 'puacauros1@gmail.com')->first();
        if ($client) {
            Project::factory()->count(2)->create([
                'client_id' => $client->id,
                'status' => 'open',
            ]);
        }

        // Još projekata za ostale klijente
        $otherClients = User::where('role', 'client')->pluck('id');
        Project::factory()->count(5)->create([
            'client_id' => $otherClients->random(),
        ]);
    }
}
