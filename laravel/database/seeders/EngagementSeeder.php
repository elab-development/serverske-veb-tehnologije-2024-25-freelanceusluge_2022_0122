<?php

namespace Database\Seeders;

use App\Models\Bid;
use App\Models\Engagement;
use App\Models\Project;
use Illuminate\Database\Seeder;

class EngagementSeeder extends Seeder
{
    public function run(): void
    {
        // Pokušaj da nađeš accepted bid i napraviš engagement
        $accepted = Bid::where('status', 'accepted')->inRandomOrder()->first();

        if ($accepted) {
            $project = Project::find($accepted->project_id);
            if ($project) {
                Engagement::factory()->create([
                    'project_id' => $project->id,
                    'bid_id' => $accepted->id,
                    'provider_id' => $accepted->provider_id,
                    'client_id' => $project->client_id,
                    'agreed_amount' => $accepted->amount ?? $accepted->ammount ?? 500,
                    'state' => 'active',
                ]);
            }
        }

        // Par dodatnih engagement-a iz factory-ja (sam kreira konzistentne entitete)
        Engagement::factory()->count(2)->create();
    }
}
