<?php

namespace Database\Factories;

use App\Models\Bid;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BidFactory extends Factory
{
    protected $model = Bid::class;

    public function definition(): array
    {
        $project = Project::factory()->open();

        return [
            'project_id' => $project,
            'provider_id' => User::factory()->provider(),
            'amount' => $this->faker->randomFloat(2, 150, 5000),
 
            'message' => $this->faker->optional()->sentence(10),
            'status' => $this->faker->randomElement(['pending','accepted','rejected','withdrawn']),
            'days_to_complete' => $this->faker->numberBetween(1, 30),
     
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'pending']);
    }

    public function accepted(): static
    {
        return $this->state(fn () => ['status' => 'accepted']);
    }
}
