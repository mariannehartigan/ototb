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
        Schema::create('planned_expenses', function (Blueprint $table) {
            $table->id();
            $table->text('description')->nullable();
            $table->boolean('tithe')->default(true);
            $table->decimal('amount', 12, 2)->nullable();
            $table->text('frequency')->nullable();
            $table->text('day_due')->nullable();
            $table->boolean('automatic_payment')->default(false);
            $table->text('account_taken_from')->default('debit');
            $table->text('notes')->nullable();
            $table->foreignIdFor(\App\Models\User::class, 'user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planned_expenses');
    }
};
