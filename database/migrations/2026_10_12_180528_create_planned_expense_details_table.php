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
        Schema::create('planned_expense_details', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\PlannedExpense::class, 'planned_expense_id')->references('id')->on('planned_expenses');
            $table->foreignIdFor(\App\Models\PlannedIncome::class, 'planned_income_id')->nullable()->references('id')->on('planned_incomes'); 
            $table->text('frequency')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->text('account_taken_from')->nullable();
            $table->string('fixed_or_variable')->nullable();
            $table->string('manual_or_automatic')->nullable();
            $table->boolean('is_five_budget_month_savings')->default(false);
            $table->text('day_of_week_for_weekly_biweekly')->nullable();
            $table->unsignedInteger('day_due_for_monthly')->nullable();
            $table->date('due_date_for_biweekly_quarterly_yearly_onetime')->nullable();
            $table->date('effective_date')->default(now())->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planned_expense_details');
    }
};
