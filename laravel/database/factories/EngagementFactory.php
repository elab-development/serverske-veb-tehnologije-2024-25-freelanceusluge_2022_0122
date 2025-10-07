<?php

namespace Database\Factories;

use App\Models\Engagement;
use App\Models\Project;
use App\Models\Bid;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EngagementFactory extends Factory
{
    protected $model = Engagement::class;

    public function definition(): array
    {
        
        $project = Project::factory()->open()->create();  
        $provider = User::factory()->provider()->create();

        $bid = Bid::factory()->accepted()->create([
            'project_id' => $project->id,
            'provider_id' => $provider->id,
        ]);

        $amount = $this->faker->randomFloat(2, $project->budget_min ?? 200, $project->budget_max ?? 5000);

        $start = $this->faker->dateTimeBetween('-10 days', 'now');
        $end = $this->faker->boolean(40) ? $this->faker->dateTimeBetween($start, '+20 days') : null;
        $state = $end ? $this->faker->randomElement(['completed','cancelled']) : 'active';

        return [
            'project_id' => $project->id,
            'bid_id' => $bid->id,
            'provider_id' => $provider->id,
            'client_id' => $project->client_id,
            'agreed_amount' => $amount,
            'started_at' => $start,
            'ended_at' => $end,
            'state' => $state,
        ];
    }
}
