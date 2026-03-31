<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $min = $this->faker->randomFloat(2, 100, 1000);
        $max = $min + $this->faker->randomFloat(2, 100, 2000);

        return [
            'client_id' => User::factory()->client(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraphs(2, true),
            'budget_min' => $min,
            'budget_max' => $max,
            'status' => $this->faker->randomElement(['open', 'in_progress', 'closed']),
            'tags' => $this->faker->randomElements(['laravel','react','api','docker','sql','aws'], rand(1,3)),
        ];
    }

    public function open(): static
    {
        return $this->state(fn () => ['status' => 'open']);
    }
}
