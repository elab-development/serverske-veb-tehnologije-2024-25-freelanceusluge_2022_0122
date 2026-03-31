<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            ['name' => 'Laravel', 'category' => 'backend'],
            ['name' => 'React',   'category' => 'frontend'],
            ['name' => 'Angular', 'category' => 'frontend'],
            ['name' => 'Node.js', 'category' => 'backend'],
            ['name' => 'PHP',     'category' => 'backend'],
            ['name' => 'SQL',     'category' => 'data'],
            ['name' => 'Docker',  'category' => 'devops'],
            ['name' => 'AWS',     'category' => 'devops'],
        ];

        foreach ($skills as $s) {
            Skill::firstOrCreate(['name' => $s['name']], ['category' => $s['category']]);
        }

        
    }
}
