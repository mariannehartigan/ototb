<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::factory()->create([
            'name' => 'Test User 2',
            'email' => 'vilt.test.inactive@gmail.com',
        ]);        

        \App\Models\Category::insert([
          ['day' => 'Today'],
          ['day' => 'Tomorrow'],
          ['day' => 'Next Week'],
        ]);

        \App\Models\Todo::factory(20)->create([
          'user_id' => 1
        ]);

        \App\Models\Todo::factory(10)->create([
          'user_id' => 2
        ]);

        \App\Models\ToDo::factory()->create([
          'description' => 'Collect a leaf from Japan',
          'user_id' => 1
        ]);
    }
}
