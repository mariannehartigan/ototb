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
        Schema::create('planned_budgets', function (Blueprint $table) {
            $table->id();
            $table->text('description')->nullable();
            $table->date('start_date')->default(now())->nullable();
            $table->date('end_date')->nullable();            
            $table->text('notes')->nullable();            $table->boolean('is_five_budget_month_savings')->default(false);
            $table->unsignedInteger('budget_position')->default(0);           
            $table->foreignIdFor(\App\Models\User::class, 'user_id')->references('id')->on('users'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planned_budgets');
    }
};
