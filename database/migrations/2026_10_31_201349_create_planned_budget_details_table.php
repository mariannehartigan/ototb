<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('planned_budget_details', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\PlannedExpense::class, 'planned_budget_id')->references('id')->on('planned_budgets');
            $table->foreignIdFor(\App\Models\PlannedIncome::class, 'planned_income_id')->nullable()->references('id')->on('planned_incomes'); 
            $table->text('frequency')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->text('account_taken_from')->nullable();
            $table->boolean('tithe')->default(false);
            $table->unsignedInteger('day_due_for_monthly')->nullable();
            $table->date('due_date_for_biweekly_quarterly_yearly_onetime')->nullable();
            $table->date('effective_date')->default(now())->nullable();   
            $table->date('last_date_before_change')->default(now())->nullable();   
            $table->date('first_date_of_change')->default(now())->nullable();            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planned_budget_details');
    }
};
