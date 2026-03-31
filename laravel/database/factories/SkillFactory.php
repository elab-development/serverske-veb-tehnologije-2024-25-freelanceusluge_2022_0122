<?php

namespace Database\Factories;

use App\Models\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;

class SkillFactory extends Factory
{
    protected $model = Skill::class;

    public function definition(): array
    {
        $names = ['Laravel', 'React', 'Vue', 'Angular', 'Node.js', 'PHP', 'Java', 'Python', 'SQL', 'Docker', 'AWS'];
        return [
            'name' => $this->faker->unique()->randomElement($names),
            'category' => $this->faker->randomElement(['backend', 'frontend', 'devops', 'data']),
        ];
    }
}
