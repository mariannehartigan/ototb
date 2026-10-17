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
        Schema::create('planned_income_details', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\PlannedIncome::class, 'planned_income_id')->references('id')->on('planned_incomes');
            $table->text('frequency')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->text('day_of_week_for_weekly')->nullable();
            $table->integer('day_deposited_for_monthly')->nullable();
            $table->date('deposit_date_for_biweekly_quarterly_yearly_onetime')->nullable();
            $table->boolean('tithe')->nullable();
            $table->date('effective_date')->default(now())->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planned_income_details');
    }
};
