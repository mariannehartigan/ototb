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

        \App\Models\Income::insert([
          [
            'description'   => 'Hack the Box',
            'amount'        => 2457.64,
            'day_deposited' => 31,
            'frequency'     => 'monthly',
            'user_id'       => 1,
          ],
          [
            'description'   => 'Hack the Box',
            'amount'        => 2464.27,
            'day_deposited' => 15,
            'frequency'     => 'monthly',
            'user_id'       => 1,
          ],
          [
            'description'   => 'CPP',
            'amount'        => 2230.49,
            'day_deposited' => 30,
            'frequency'     => 'monthly',
            'user_id'       => 1,
          ],
          [
            'description'   => 'CCB',
            'amount'        => 1338.44,
            'day_deposited' => 20,
            'frequency'     => 'monthly',
            'user_id'       => 1,
          ],
          [
            'description'   => 'Trillium',
            'amount'        => 32.21,
            'day_deposited' => 10,
            'frequency'     => 'monthly',
            'user_id'       => 1,
          ],                              
        ]);     

    }
}
