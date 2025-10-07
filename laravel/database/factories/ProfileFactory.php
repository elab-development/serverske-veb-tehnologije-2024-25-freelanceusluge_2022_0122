<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->provider(),  
            'headline' => $this->faker->jobTitle(),
            'bio' => $this->faker->paragraph(),
            'github_url' => 'https://github.com/' . $this->faker->userName(),
            'portfolio_url' => $this->faker->url(),
            'avatar_path' => 'profiles/' . $this->faker->numberBetween(1, 5000) . '/avatar.jpg',
            'banner_path' => 'profiles/' . $this->faker->numberBetween(1, 5000) . '/banner.jpg',
            'links' => [
                $this->faker->url(),
                $this->faker->url(),
            ],
        ];
    }
 


}
