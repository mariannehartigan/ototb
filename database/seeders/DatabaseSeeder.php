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

        
        \App\Models\Setting::create([
            'tithe'         => true,
            'use_budget'    => true,
            'budget_day_of_week'    => 'Friday',
            'save_for_five_budget_months'   => true,
            'user_id'       => 1,
        ]);


        \App\Models\PlannedIncome::create([
            'description'   => 'Silver Lining Ventures',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-08-01',
            'notes'         => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 1,
            'amount'        => 300.00,
            'frequency'     => 'weekly',
            'day_of_week_for_weekly'    => 'Wednesday',
            'tithe'         => true,
            'effective_date'    => '2026-08-01',
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 1,
            'amount'        => 250.00,
            'frequency'     => 'weekly',
            'day_of_week_for_weekly'    => 'Thursday',
            'tithe'         => true,
            'effective_date'    => '2026-10-01',
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 1,
            'amount'        => 200.00,
            'frequency'     => 'weekly',
            'day_of_week_for_weekly'    => 'Tuesday',
            'effective_date'    => '2027-01-01',
            'tithe'         => true,
        ]);

        \App\Models\PlannedIncome::create([
            'description' => 'Solid Ground Landscaping',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-08-01',
            'notes'       => 'notes',
            'user_id'   => 1,
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 2,
            'amount'        => 300.00,
            'frequency'     => 'biweekly',
            'deposit_date_for_biweekly_quarterly_yearly_onetime'    => '2026-08-01',
            'effective_date'    => '2026-08-01',
            'tithe'         => true,
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 2,
            'amount'        => 250.00,
            'frequency'     => 'biweekly',
            'deposit_date_for_biweekly_quarterly_yearly_onetime'    => '2026-10-01',
            'effective_date'    => '2026-10-01',
            'tithe'         => true,
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 2,
            'amount'        => 200.00,
            'frequency'     => 'biweekly',
            'deposit_date_for_biweekly_quarterly_yearly_onetime'    => '2027-01-01',
            'effective_date'    => '2027-01-01',
            'tithe'         => true,
        ]);

        \App\Models\PlannedIncome::create([
            'description' => 'Bright Side Analytics',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-08-01',
            'notes'       => 'notes',
            'user_id' => 1,
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 3,
            'amount'        => 75.00,
            'frequency'     => 'monthly',
            'day_deposited_for_monthly' => '15',
            'effective_date'    => '2026-08-01',
            'tithe'         => true,
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 3,
            'amount'        => 175.00,
            'frequency'     => 'monthly',
            'day_deposited_for_monthly' => '1',
            'effective_date'    => '2026-10-01',
            'tithe'         => true,
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 3,
            'amount'        => 275.00,
            'frequency'     => 'monthly',
            'day_deposited_for_monthly' => '25',
            'effective_date'    => '2027-01-01',
            'tithe'         => true,
        ]);

        \App\Models\PlannedIncome::create([
            'description' => 'Bright Side Analytics',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-08-01',
            'notes'       => 'notes',
            'user_id' => 1,
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 4,
            'amount'        => 75.00,
            'frequency'     => 'monthly',
            'day_deposited_for_monthly' => '30',
            'effective_date'    => '2026-08-01',
            'tithe'         => true,
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 4,
            'amount'        => 175.00,
            'frequency'     => 'monthly',
            'day_deposited_for_monthly' => '20',
            'effective_date'    => '2026-10-01',
            'tithe'         => true,
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 4,
            'amount'        => 275.00,
            'frequency'     => 'monthly',
            'day_deposited_for_monthly' => '25',
            'effective_date'    => '2027-01-01',
            'tithe'         => true,
        ]);

        \App\Models\PlannedIncome::create([
            'description' => 'Brain Wave Tutoring',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-08-01',
            'notes'       => 'notes',
            'user_id' => 1,
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 5,
            'amount'        => 1000.00,
            'frequency'     => 'quarterly',
            'deposit_date_for_biweekly_quarterly_yearly_onetime'    => '2026-08-01',
            'effective_date'    => '2026-08-01',
            'tithe'         => true,
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 5,
            'amount'        => 2000.00,
            'frequency'     => 'quarterly',
            'deposit_date_for_biweekly_quarterly_yearly_onetime'    => '2026-10-01',
            'effective_date'    => '2026-10-01',
            'tithe'         => true,
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 5,
            'amount'        => 3000.00,
            'frequency'     => 'quarterly',
            'deposit_date_for_biweekly_quarterly_yearly_onetime'    => '2027-01-01',
            'effective_date'    => '2027-01-01',
            'tithe'         => true,
        ]);

        \App\Models\PlannedIncome::create([
            'description' => 'Good Sport Fitness',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-08-01',
            'notes'       => 'notes',
            'user_id' => 1,
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 6,
            'amount'        => 100.00,
            'frequency' => 'yearly', 
            'deposit_date_for_biweekly_quarterly_yearly_onetime'    => '2026-08-01',
            'effective_date'    => '2026-08-01',
            'tithe'         => true,
        ]);

        \App\Models\PlannedIncome::create([
            'description' => 'Balancing Act Bookkeeping',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-08-01',
            'notes'       => 'notes',
            'user_id' => 1,
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 7,
            'amount'        => 250.00,
            'frequency'     => 'onetime',
            'deposit_date_for_biweekly_quarterly_yearly_onetime'    => '2026-09-01',
            'effective_date'    => '2026-09-01',
            'tithe'         => true,
        ]);
        
        \App\Models\PlannedIncome::create([
            'description' => 'Lapcats R Us',
            'start_date'  => '2026-10-01',
            'end_date'    => '2026-11-15',
            'notes'       => 'notes',
            'user_id' => 1,
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 8,
            'amount'        => 200.00,
            'frequency'     => 'biweekly',
            'deposit_date_for_biweekly_quarterly_yearly_onetime'    => '2026-01-01',
            'effective_date'    => '2026-01-01',
            'tithe'         => true,
        ]);

         /* \App\Models\PlannedIncome::create([
            'description'   => 'Hack the Box',
            // 'amount'        => 2457.64,
            // 'day_deposited_for_monthly' => 31,
            // 'frequency'     => 'monthly',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedIncome::create([
            'description'   => 'Hack the Box',
            // 'amount'        => 2464.27,
            // 'day_deposited_for_monthly' => 15,
            // 'frequency'     => 'monthly',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedIncome::create([
            'description'   => 'CPP',
            // 'amount'        => 2230.49,
            // 'day_deposited_for_monthly' => 30,
            // 'frequency'     => 'monthly',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedIncome::create([
            'description'   => 'CCB',
            // 'amount'        => 1338.44,
            // 'day_deposited_for_monthly' => 20,
            // 'frequency'     => 'monthly',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedIncome::create([
            'description'   => 'Trillium',
            // 'amount'        => 55.10,
            // 'day_deposited_for_monthly' => 10,
            // 'frequency'     => 'yearly',
            'user_id'       => 1,
        ]); */


        \App\Models\PlannedExpense::create([
            'description'   => 'Mortgage',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-08-01',
            'tithe'         => false,
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpenseDetail::create([
            'planned_expense_id'    => '1',
            'planned_income_id'     =>  '5',
            'frequency'     => 'monthly',
            'amount'        => 1771.12,
            'fixed_or_variable' => 'fixed',
            'manual_or_automatic'  => 'automatic',
            'day_due_for_monthly'       => 23,
            'account_taken_from'  => 'RBC',
            'effective_date'    => '2026-01-01',
        ]);
        \App\Models\PlannedExpenseDetail::create([
            'planned_expense_id'    => '1',
            'planned_income_id'     =>  '5',
            'frequency'     => 'monthly',
            'amount'        => 1971.12,
            'fixed_or_variable' => 'fixed',
            'manual_or_automatic'  => 'automatic',
            'day_due_for_monthly'       => 23,
            'account_taken_from'  => 'RBC',
            'effective_date'    => '2026-11-01',
        ]);

        \App\Models\PlannedExpense::create([
            'description'   => 'House Insurance',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-08-01',
            'tithe'         => false,
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpenseDetail::create([
            'planned_expense_id'    => '2',
            'frequency'     => 'monthly',
            'amount'        => 159.46,
            'fixed_or_variable' => 'fixed',
            'manual_or_automatic'  => 'automatic',
            'day_due_for_monthly'       => 5,
            'effective_date'    => '2026-01-01',
        ]);
        
        \App\Models\PlannedExpense::create([
            'description'   => 'Car Insurance',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-08-01',
            'tithe'         => false,
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpenseDetail::create([
            'planned_expense_id'    => '3',
            'frequency'     => 'monthly',
            'amount'        => 259.60,
            'fixed_or_variable' => 'fixed',
            'manual_or_automatic'  => 'automatic',
            'day_due_for_monthly'       => 5,
            'effective_date'    => '2026-01-01',
        ]);

        \App\Models\PlannedExpense::create([
            'description'   => 'Property Tax',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-08-01',
            'tithe'         => false,
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpenseDetail::create([
            'planned_expense_id'    => '4',
            'frequency'     => 'monthly',
            'amount'        => 333.96,
            'fixed_or_variable' => 'fixed',
            'manual_or_automatic'  => 'automatic',
            'day_due_for_monthly'       => 15,
            'effective_date'    => '2026-01-01',
        ]);
        
        \App\Models\PlannedExpense::create([
            'description'   => 'Hydro',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-08-01',
            'tithe'         => false,
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpenseDetail::create([
            'planned_expense_id'    => '5',
            'frequency'     => 'monthly',
            'amount'        => 315,
            'fixed_or_variable' => 'fixed',
            'manual_or_automatic'  => 'manual',
            'day_due_for_monthly'       => 25,
            'effective_date'    => '2026-01-01',
        ]);
        
        \App\Models\PlannedExpense::create([
            'description'   => 'Enbridge',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-08-01',
            'tithe'         => false,
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpenseDetail::create([
            'planned_expense_id'    => '6',
            'frequency'     => 'monthly',
            'amount'        => 80,
            'fixed_or_variable' => 'fixed',
            'manual_or_automatic'  => 'manual',
            'day_due_for_monthly'       => 25,
            'effective_date'    => '2026-01-01',
        ]);
                
        \App\Models\PlannedExpense::create([
            'description'   => 'Water',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-08-01',
            'tithe'         => false,
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpenseDetail::create([
            'planned_expense_id'    => '7',
            'frequency'     => 'monthly',
            'amount'        => 200,
            'fixed_or_variable' => 'fixed',
            'manual_or_automatic'  => 'automatic',
            'day_due_for_monthly'       => 25,
            'effective_date'    => '2026-01-01',
        ]);
                
        \App\Models\PlannedExpense::create([
            'description'   => 'Bell',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-08-01',
            'tithe'         => false,
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpenseDetail::create([
            'planned_expense_id'    => '8',
            'frequency'     => 'monthly',
            'amount'        => 420,
            'fixed_or_variable' => 'fixed',
            'manual_or_automatic'  => 'automatic',
            'day_due_for_monthly'       => 20,
            'effective_date'    => '2026-01-01',
        ]);
                
        \App\Models\PlannedExpense::create([
            'description'   => 'Allan giving',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-08-01',
            // 'tithe'         => true,
            'tithe'         => true,
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpenseDetail::create([
            'planned_expense_id'    => '9',
            'planned_income_id'     => '3',
            'frequency'     => 'monthly',
            'amount'        => 50,
            'fixed_or_variable' => 'fixed',
            'manual_or_automatic'  => 'manual',
            'day_due_for_monthly'       => 31,
            'effective_date'    => '2026-01-01',
        ]);
        \App\Models\PlannedExpenseDetail::create([
            'planned_expense_id'    => '9',
            'frequency'     => 'monthly',
            'amount'        => 60,
            'fixed_or_variable' => 'fixed',
            'manual_or_automatic'  => 'manual',
            'day_due_for_monthly'       => 31,
            'effective_date'    => '2026-10-25',
        ]);
        \App\Models\PlannedExpenseDetail::create([
            'planned_expense_id'    => '9',
            'frequency'     => 'monthly',
            'amount'        => 70,
            'fixed_or_variable' => 'fixed',
            'manual_or_automatic'  => 'manual',
            'day_due_for_monthly'       => 31,
            'effective_date'    => '2026-10-31',
        ]);        
                
        \App\Models\PlannedExpense::create([
            'description'   => 'St. Peter',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-08-01',
            'tithe'         => true,
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpenseDetail::create([
            'planned_expense_id'    => '10',
            'planned_income_id'     => '3',
            'frequency'     => 'monthly',
            'amount'        => 90,
            'fixed_or_variable' => 'fixed',
            'manual_or_automatic'  => 'automatic',
            'day_due_for_monthly'       => 1,
            'effective_date'    => '2026-01-01',
        ]);
                
        \App\Models\PlannedExpense::create([
            'description'   => 'St. Francis',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-08-01',
            // 'tithe'         => true,
            'tithe'         => true,
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpenseDetail::create([
            'planned_expense_id'    => '11',
            'planned_income_id'     => '3',
            'frequency'     => 'monthly',
            'amount'        => 20,
            'fixed_or_variable' => 'fixed',
            'manual_or_automatic'  => 'automatic',
            'day_due_for_monthly'       => 31,
            'effective_date'    => '2026-01-01',
        ]);
                
        \App\Models\PlannedExpense::create([
            'description'   => 'Netflix',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-08-01',
            'tithe'         => false,
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpenseDetail::create([
            'planned_expense_id'    => '12',
            'frequency'     => 'monthly',
            'amount'        => 28,
            'fixed_or_variable' => 'fixed',
            'manual_or_automatic'  => 'automatic',
            'account_taken_from'  => 'WM card',
            'day_due_for_monthly'       => 22,
            'effective_date'    => '2026-01-01',
        ]);
                
        \App\Models\PlannedExpense::create([
            'description'   => 'YouTube 1',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-08-01',
            'tithe'         => false,
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpenseDetail::create([
            'planned_expense_id'    => '13',
            'frequency'     => 'monthly',
            'amount'        => 25.98,
            'fixed_or_variable' => 'fixed',
            'manual_or_automatic'  => 'automatic',
            'account_taken_from'  => 'WM card',
            'day_due_for_monthly'       => 26,
            'effective_date'    => '2026-01-01',
        ]);
                
        \App\Models\PlannedExpense::create([
            'description'   => 'YouTube 2',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-08-01',
            'tithe'         => false,
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpenseDetail::create([
            'planned_expense_id'    => '14',
            'frequency'     => 'monthly',
            'amount'        => 14.68,
            'fixed_or_variable' => 'fixed',
            'manual_or_automatic'  => 'automatic',
            'account_taken_from'  => 'WM card',
            'day_due_for_monthly'       => 10,
            'effective_date'    => '2026-01-01',
        ]);
                
        \App\Models\PlannedExpense::create([
            'description'   => 'Spotify',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-08-01',
            'tithe'         => false,
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpenseDetail::create([
            'planned_expense_id'    => '15',
            'frequency'     => 'monthly',
            'amount'        => 28,
            'fixed_or_variable' => 'fixed',
            'manual_or_automatic'  => 'automatic',
            'account_taken_from'  => 'WM card',
            'day_due_for_monthly'       => 12,
            'effective_date'    => '2026-01-01',
        ]);
                
        \App\Models\PlannedExpense::create([
            'description'   => 'Loan interest',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-08-01',
            'tithe'         => false,
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpenseDetail::create([
            'planned_expense_id'    => '16',
            'frequency'     => 'monthly',
            'amount'        => 260,
            'fixed_or_variable' => 'fixed',
            'manual_or_automatic'  => 'manual',
            'day_due_for_monthly'       => 28,
            'effective_date'    => '2026-01-01',
        ]);
        
        /*        
        \App\Models\PlannedExpense::create([
            'description'   => 'Groceries',
            'amount'        => 340,
            'frequency'     => 'weekly',
            'fixed_or_variable' => 'variable',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Garbage tags',
            'amount'        => 10,
            'frequency'     => 'weekly',
            'fixed_or_variable' => 'variable',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Gas',
            'amount'        => 85,
            'frequency'     => 'weekly',
            'fixed_or_variable' => 'variable',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Allan',
            'amount'        => 80,
            'frequency'     => 'weekly',
            'fixed_or_variable' => 'variable',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Marianne',
            'amount'        => 60,
            'frequency'     => 'weekly',
            'fixed_or_variable' => 'variable',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Brianna',
            'amount'        => 0,
            'frequency'     => 'weekly',
            'fixed_or_variable' => 'variable',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Autumn',
            'amount'        => 0,
            'frequency'     => 'weekly',
            'fixed_or_variable' => 'variable',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Kristen',
            'amount'        => 20,
            'frequency'     => 'weekly',
            'fixed_or_variable' => 'variable',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Caragh',
            'amount'        => 20,
            'frequency'     => 'weekly',
            'fixed_or_variable' => 'variable',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Angelica',
            'amount'        => 20,
            'frequency'     => 'weekly',
            'fixed_or_variable' => 'variable',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Kids school',
            'amount'        => 50,
            'frequency'     => 'weekly',
            'fixed_or_variable' => 'variable',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Mass',
            'amount'        => 20,
            'frequency'     => 'weekly',
            'fixed_or_variable' => 'variable',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Gifts',
            'amount'        => 630,
            'frequency'     => 'monthly',
            'fixed_or_variable' => 'variable',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Prescriptions',
            'amount'        => 35,
            'frequency'     => 'monthly',
            'fixed_or_variable' => 'variable',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Weekly expense',
            'amount'        => 350,
            'frequency'     => 'weekly',
            'day_of_week_for_weekly_fixed' => 'Wednesday',
            'fixed_or_variable' => 'variable',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Quarterly expense',
            'amount'        => 350,
            'frequency'     => 'quarterly',
            'fixed_or_variable' => 'variable',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Yearly expense',
            'amount'        => 500,
            'frequency'     => 'yearly',
            'fixed_or_variable' => 'variable',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'One-time expense',
            'amount'        => 1000,
            'frequency'     => 'one-time',
            'fixed_or_variable' => 'variable',
            'user_id'       => 1,
        ]); */

    }
}
