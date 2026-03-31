<?php

namespace Database\Seeders;

use App\Models\Bid;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class BidSeeder extends Seeder
{
    public function run(): void
    {
        $providers = User::where('role', 'provider')->pluck('id');
        $projects = Project::where('status', 'open')->pluck('id');

        if ($providers->isEmpty() || $projects->isEmpty()) {
            return;
        }

        // Za svaki open projekat, 2–4 ponude
        foreach ($projects as $pid) {
            Bid::factory()->count(rand(2,4))->create([
                'project_id' => $pid,
                'provider_id' => $providers->random(),
                // 'notes' se puni u factory-ju ako kolona još postoji
            ]);
        }

        // Jedna eksplicitno accepted ponuda
        $proj = Project::inRandomOrder()->first();
        if ($proj) {
            Bid::factory()->accepted()->create([
                'project_id' => $proj->id,
                'provider_id' => $providers->random(),
            ]);
        }
    }
}
