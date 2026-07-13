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

        \App\Models\PlannedIncome::insert([
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

        \App\Models\Budget::insert([
          [
            'description'   => 'Groceries',
            'amount'        => 340,
            'frequency'     => 'weekly',
            'user_id'       => 1,
          ],
          [
            'description'   => 'Garbage tags',
            'amount'        => 10,
            'frequency'     => 'weekly',
            'user_id'       => 1,
          ],
          [
            'description'   => 'Gas',
            'amount'        => 85,
            'frequency'     => 'weekly',
            'user_id'       => 1,
          ],
          [
            'description'   => 'Allan',
            'amount'        => 80,
            'frequency'     => 'weekly',
            'user_id'       => 1,
          ],
          [
            'description'   => 'Marianne',
            'amount'        => 60,
            'frequency'     => 'weekly',
            'user_id'       => 1,
          ],
          [
            'description'   => 'Brianna',
            'amount'        => 0,
            'frequency'     => 'weekly',
            'user_id'       => 1,
          ],
          [
            'description'   => 'Autumn',
            'amount'        => 0,
            'frequency'     => 'weekly',
            'user_id'       => 1,
          ],
          [
            'description'   => 'Kristen',
            'amount'        => 20,
            'frequency'     => 'weekly',
            'user_id'       => 1,
          ],
          [
            'description'   => 'Caragh',
            'amount'        => 20,
            'frequency'     => 'weekly',
            'user_id'       => 1,
          ],
          [
            'description'   => 'Angelica',
            'amount'        => 20,
            'frequency'     => 'weekly',
            'user_id'       => 1,
          ],
          [
            'description'   => 'Kids school',
            'amount'        => 50,
            'frequency'     => 'weekly',
            'user_id'       => 1,
          ],
          [
            'description'   => 'Mass',
            'amount'        => 25,
            'frequency'     => 'weekly',
            'user_id'       => 1,
          ],               
        ]);

// used insert instead of create in case columns are added or removed
        \App\Models\Setting::create([
            'tithe'         => true,
            'use_budget'    => true,
            'budget_day_of_week'    => 'Friday',
            'budget_type'   => 'averaged',
            'user_id'       => 1,
        ]);

        \App\Models\PlannedExpense::create([
            'description'   => 'Mortgage',
            'amount'        => 1771.12,
            'day_due'       => 31,
            'frequency'     => 'monthly',
            'automatic_payment'  => true,
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'House Insurance',
            'amount'        => 159.46,
            'day_due'       => 5,
            'frequency'     => 'monthly',
            'automatic_payment'  => true,
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Car Insurance',
            'amount'        => 259.60,
            'day_due'       => 5,
            'frequency'     => 'monthly',
            'automatic_payment'  => true,
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Property Tax',
            'amount'        => 333.96,
            'day_due'       => 15,
            'frequency'     => 'monthly',
            'automatic_payment'  => true,
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Hydro',
            'amount'        => 315,
            'day_due'       => 25,
            'frequency'     => 'monthly',
            'automatic_payment'  => false,
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Enbridge',
            'amount'        => 80,
            'day_due'       => 25,
            'frequency'     => 'monthly',
            'automatic_payment'  => false,
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Water',
            'amount'        => 200,
            'day_due'       => 25,
            'frequency'     => 'monthly',
            'automatic_payment'  => true,
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Bell',
            'amount'        => 420,
            'day_due'       => 20,
            'frequency'     => 'monthly',
            'automatic_payment'  => true,
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Allan giving',
            'tithe'         => true,
            'amount'        => 200,
            'day_due'       => 31,
            'frequency'     => 'monthly',
            'automatic_payment'  => true,
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Holy Rosary',
            'tithe'         => true,
            'amount'        => 30,
            'day_due'       => 1,
            'frequency'     => 'monthly',
            'automatic_payment'  => true,
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'St. Peter',
            'tithe'         => true,
            'amount'        => 110,
            'day_due'       => 1,
            'frequency'     => 'monthly',
            'automatic_payment'  => true,
            'account_taken_from'  => 'debit',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'St. Francis',
            'tithe'         => true,
            'amount'        => 35,
            'day_due'       => 31,
            'frequency'     => 'monthly',
            'automatic_payment'  => true,
            'account_taken_from'  => 'WM card',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'St. Joseph',
            'tithe'         => true,
            'amount'        => 20,
            'day_due'       => 15,
            'frequency'     => 'monthly',
            'automatic_payment'  => true,
            'account_taken_from'  => 'debit',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Netflix',
            'amount'        => 27.11,
            'day_due'       => 22,
            'frequency'     => 'monthly',
            'automatic_payment'  => true,
            'account_taken_from'  => 'WM card',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'YouTube 1',
            'amount'        => 25.98,
            'day_due'       => 26,
            'frequency'     => 'monthly',
            'automatic_payment'  => true,
            'account_taken_from'  => 'WM card',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'YouTube 2',
            'amount'        => 14.68,
            'day_due'       => 10,
            'frequency'     => 'monthly',
            'automatic_payment'  => true,
            'account_taken_from'  => 'Triangle',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Spotify',
            'amount'        => 23.72,
            'day_due'       => 12,
            'frequency'     => 'monthly',
            'automatic_payment'  => true,
            'account_taken_from'  => 'WM card',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Loan interest',
            'amount'        => 60,
            'day_due'       => 20,
            'frequency'     => 'monthly',
            'automatic_payment'  => true,
            'account_taken_from' => 'debit',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Gifts',
            'amount'        => 630,
            'frequency'     => 'monthly',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Prescriptions',
            'amount'        => 35,
            'frequency'     => 'monthly',
            'user_id'       => 1,
        ]);      
    }
}
