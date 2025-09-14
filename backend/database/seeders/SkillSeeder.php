<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Skill;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Skill::create(['name' => 'Web Development', 'category' => 'IT']);
        Skill::create(['name' => 'Graphic Design', 'category' => 'Design']);
        Skill::create(['name' => 'Cooking', 'category' => 'Culinary']);
    }
}
