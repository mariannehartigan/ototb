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

/*         \App\Models\PlannedIncome::create([
            'description'   => 'IncomeOne',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-08-01',
            'notes'         => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 1,
            'amount'        => 200.00,
            'frequency'     => 'monthly',
            'day_deposited_for_monthly' => '1',
            'tithe'         => true,
            'effective_date'    => '2026-08-01',
        ]);
        
        \App\Models\PlannedIncome::create([
            'description'   => 'IncomeTwo',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-08-01',
            'notes'         => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 2,
            'amount'        => 150.00,
            'frequency'     => 'monthly',
            'day_deposited_for_monthly' => '14',
            'tithe'         => true,
            'effective_date'    => '2026-08-01',
        ]);
        
        \App\Models\PlannedIncome::create([
            'description'   => 'IncomeThree',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-08-01',
            'notes'         => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 3,
            'amount'        => 650.00,
            'frequency'     => 'monthly',
            'day_deposited_for_monthly' => '25',
            'tithe'         => true,
            'effective_date'    => '2026-08-01',
        ]);     

        \App\Models\PlannedIncome::create([
            'description'   => 'IncomeFour',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-08-01',
            'notes'         => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 4,
            'amount'        => 650.00,
            'frequency'     => 'monthly',
            'day_deposited_for_monthly' => '27',
            'tithe'         => true,
            'effective_date'    => '2026-08-01',
        ]); */



/*          \App\Models\PlannedIncome::create([
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
        ]);  */


        \App\Models\PlannedIncome::create([
            'description'   => 'Hack the Box',
            'start_date'  => '2026-10-01',
            'end_date'    => '2029-11-15',
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 1,
            'amount'        => 2457.64,
            'frequency'     => 'monthly',
            'day_deposited_for_monthly' => 31,
            'effective_date'    => '2026-01-01',
            'tithe'         => true,
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 1,
            'amount'        => 2900.64,
            'frequency'     => 'monthly',
            'day_deposited_for_monthly' => 31,
            'effective_date'    => '2027-01-01',
            'tithe'         => true,
        ]);

        \App\Models\PlannedIncome::create([
            'description'   => 'Hack the Box',
            'start_date'  => '2026-10-01',
            'end_date'    => '2029-11-15',
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 2,
            'amount'        => 2464.27,
            'frequency'     => 'monthly',
            'day_deposited_for_monthly' => 15,
            'effective_date'    => '2026-01-01',
            'tithe'         => true,
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 2,
            'amount'        => 2900.64,
            'frequency'     => 'monthly',
            'day_deposited_for_monthly' => 31,
            'effective_date'    => '2027-01-01',
            'tithe'         => true,
        ]);

        \App\Models\PlannedIncome::create([
            'description'   => 'CPP',
            'start_date'  => '2026-10-01',
            'end_date'    => '2029-11-15',
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 3,
            'amount'        => 2230.49,
            'frequency'     => 'monthly',
            'day_deposited_for_monthly' => 31,
            'effective_date'    => '2026-01-01',
            'tithe'         => true,
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 3,
            'amount'        => 1900.49,
            'frequency'     => 'monthly',
            'day_deposited_for_monthly' => 31,
            'effective_date'    => '2027-01-01',
            'tithe'         => true,
        ]);

        \App\Models\PlannedIncome::create([
            'description'   => 'CCB',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-11-15',
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 4,
            'amount'        => 1900.49,
            'frequency'     => 'monthly',
            'day_deposited_for_monthly' => 31,
            'effective_date'    => '2026-01-01',
            'tithe'         => true,
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 4,
            'amount'        => 1338.44,
            'frequency'     => 'monthly',
            'day_deposited_for_monthly' => 31,
            'effective_date'    => '2027-01-01',
            'tithe'         => true,
        ]);

        \App\Models\PlannedIncome::create([
            'description'   => 'Trillium',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-11-15',
            'notes'       => 'notes',
            'user_id'       => 1,
        ]); 
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 5,
            'amount'        => 30.27,
            'frequency'     => 'monthly',
            'day_deposited_for_monthly' => 10,
            'effective_date'    => '2026-01-01',
            'tithe'         => true,
        ]);
        \App\Models\PlannedIncomeDetail::create([
            'planned_income_id' => 5,
            'amount'        => 50.27,
            'frequency'     => 'yearly',
            'deposit_date_for_biweekly_quarterly_yearly_onetime' => '2027-07-01',
            'effective_date'    => '2027-01-01',
            'tithe'         => true,
        ]);





/*          \App\Models\PlannedExpense::create([
            'description'   => 'ExpenseOne',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-08-01',
            'tithe'         => false,
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpenseDetail::create([
            'planned_expense_id'    => '1',
            'planned_income_id'     => '2',
            'frequency'     => 'monthly',
            'amount'        => 200,
            'manual_or_automatic'  => 'automatic',
            'day_due_for_monthly'       => 3,
            'account_taken_from'  => 'RBC',
            'effective_date'    => '2026-01-01',
        ]);

         \App\Models\PlannedExpense::create([
            'description'   => 'ExpenseTwo',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-08-01',
            'tithe'         => false,
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpenseDetail::create([
            'planned_expense_id'    => '2',
            'frequency'     => 'monthly',
            'amount'        => 300,
            'manual_or_automatic'  => 'automatic',
            'day_due_for_monthly'       => 15,
            'account_taken_from'  => 'RBC',
            'effective_date'    => '2026-01-01',
        ]);
        
         \App\Models\PlannedExpense::create([
            'description'   => 'ExpenseThree',
            'start_date'  => '2026-01-01',
            'end_date'    => '2029-08-01',
            'tithe'         => false,
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpenseDetail::create([
            'planned_expense_id'    => '3',
            'frequency'     => 'monthly',
            'amount'        => 500,
            'manual_or_automatic'  => 'automatic',
            'day_due_for_monthly'       => 28,
            'account_taken_from'  => 'RBC',
            'effective_date'    => '2026-01-01',
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
            'frequency'     => 'monthly',
            'amount'        => 1771.12,
            'manual_or_automatic'  => 'automatic',
            'day_due_for_monthly'       => 23,
            'account_taken_from'  => 'RBC',
            'effective_date'    => '2026-01-01',
        ]);
        \App\Models\PlannedExpenseDetail::create([
            'planned_expense_id'    => '1',
            'frequency'     => 'monthly',
            'amount'        => 1971.12,
            'manual_or_automatic'  => 'automatic',
            'day_due_for_monthly'       => 23,
            'account_taken_from'  => 'RBC',
            'effective_date'    => '2026-11-02',
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
            'frequency'     => 'monthly',
            'amount'        => 50,
            'manual_or_automatic'  => 'manual',
            'day_due_for_monthly'       => 31,
            'effective_date'    => '2026-01-01',
        ]);
        \App\Models\PlannedExpenseDetail::create([
            'planned_expense_id'    => '9',
            'frequency'     => 'monthly',
            'amount'        => 60,
            'manual_or_automatic'  => 'manual',
            'day_due_for_monthly'       => 31,
            'effective_date'    => '2026-10-25',
        ]);
        \App\Models\PlannedExpenseDetail::create([
            'planned_expense_id'    => '9',
            'frequency'     => 'monthly',
            'amount'        => 70,
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
            'frequency'     => 'monthly',
            'amount'        => 90,
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
            'frequency'     => 'monthly',
            'amount'        => 20,
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
            'manual_or_automatic'  => 'manual',
            'day_due_for_monthly'       => 28,
            'effective_date'    => '2026-01-01',
        ]);



         
        \App\Models\PlannedBudget::create([
            'description'   => 'Groceries',
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedBudgetDetail::create([
            'planned_budget_id' => 1,
            'amount'        => 200,
            'frequency'     => 'weekly',
            'effective_date'    => '2026-01-01',
        ]);
        \App\Models\PlannedBudgetDetail::create([
            'planned_budget_id' => 1,
            'amount'        => 150,
            'frequency'     => 'weekly',
            'effective_date'    => '2026-11-15',
        ]);

        \App\Models\PlannedBudget::create([
            'description'   => 'Garbage tags',
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedBudgetDetail::create([
            'planned_budget_id' => 2,
            'amount'        => 10,
            'frequency'     => 'weekly',
            'effective_date'    => '2026-01-01',
        ]);

        \App\Models\PlannedBudget::create([
            'description'   => 'Gas',
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedBudgetDetail::create([
            'planned_budget_id' => 3,
            'amount'        => 85,
            'frequency'     => 'weekly',
            'effective_date'    => '2026-01-01',
        ]);

        \App\Models\PlannedBudget::create([
            'description'   => 'Allan',
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedBudgetDetail::create([
            'planned_budget_id' => 3,
            'amount'        => 80,
            'frequency'     => 'weekly',
            'effective_date'    => '2026-01-01',
        ]);

        \App\Models\PlannedBudget::create([
            'description'   => 'Marianne',
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedBudgetDetail::create([
            'planned_budget_id' => 4,
            'amount'        => 60,
            'frequency'     => 'weekly',
            'effective_date'    => '2026-01-01',
        ]);

        \App\Models\PlannedBudget::create([
            'description'   => 'Brianna',
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedBudgetDetail::create([
            'planned_budget_id' => 5,
            'amount'        => 0,
            'frequency'     => 'weekly',
            'effective_date'    => '2026-01-01',
        ]);

        \App\Models\PlannedBudget::create([
            'description'   => 'Autumn',
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedBudgetDetail::create([
            'planned_budget_id' => 6,
            'amount'        => 0,
            'frequency'     => 'weekly',
            'effective_date'    => '2026-01-01',
        ]);

        \App\Models\PlannedBudget::create([
            'description'   => 'Kristen',
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedBudgetDetail::create([
            'planned_budget_id' => 7,
            'amount'        => 20,
            'frequency'     => 'weekly',
            'effective_date'    => '2026-01-01',
        ]);

        \App\Models\PlannedBudget::create([
            'description'   => 'Caragh',
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedBudgetDetail::create([
            'planned_budget_id' => 8,
            'amount'        => 20,
            'frequency'     => 'weekly',
            'effective_date'    => '2026-01-01',
        ]);

        \App\Models\PlannedBudget::create([
            'description'   => 'Angelica',
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedBudgetDetail::create([
            'planned_budget_id' => 9,
            'amount'        => 20,
            'frequency'     => 'weekly',
            'effective_date'    => '2026-01-01',
        ]);

        \App\Models\PlannedBudget::create([
            'description'   => 'Kids school',
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedBudgetDetail::create([
            'planned_budget_id' => 10,
            'amount'        => 50,
            'frequency'     => 'weekly',
            'effective_date'    => '2026-01-01',
        ]);

        \App\Models\PlannedBudget::create([
            'description'   => 'Mass',
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedBudgetDetail::create([
            'planned_budget_id' => 11,
            'amount'        => 20,
            'frequency'     => 'weekly',
            'effective_date'    => '2026-01-01',
        ]);

        \App\Models\PlannedBudget::create([
            'description'   => 'Gifts',
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedBudgetDetail::create([
            'planned_budget_id' => 12,
            'amount'        => 630,
            'frequency'     => 'monthly',
            'day_due_for_monthly'   => 31,
            'effective_date'    => '2026-01-01',
        ]);

        \App\Models\PlannedBudget::create([
            'description'   => 'Prescriptions',
            'notes'       => 'notes',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedBudgetDetail::create([
            'planned_budget_id' => 13,
            'amount'        => 35,
            'frequency'     => 'monthly',
            'day_due_for_monthly'   => 31,
            'effective_date'    => '2026-01-01',
        ]);


/*         \App\Models\PlannedExpense::create([
            'description'   => 'Weekly expense',
            'amount'        => 350,
            'frequency'     => 'weekly',
            'day_of_week_for_weekly_fixed' => 'Wednesday',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Quarterly expense',
            'amount'        => 350,
            'frequency'     => 'quarterly',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'Yearly expense',
            'amount'        => 500,
            'frequency'     => 'yearly',
            'user_id'       => 1,
        ]);
        \App\Models\PlannedExpense::create([
            'description'   => 'One-time expense',
            'amount'        => 1000,
            'frequency'     => 'one-time',
            'user_id'       => 1,
        ]);  */

    }
}
