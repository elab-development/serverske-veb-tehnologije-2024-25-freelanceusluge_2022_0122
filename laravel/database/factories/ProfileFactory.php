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
    //poziva se uvek kada pozivamo faktori sa create
    public function configure()
    {
        return $this->afterCreating(function (\App\Models\Profile $profile) {
            // napravi par skillova ili uzmi postojeće
            $skills = \App\Models\Skill::factory()->count(rand(2,4))->create();
            $profile->skills()->sync($skills->pluck('id')->all());
        });
    }


}
