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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->text('description')->nullable();
            $table->boolean('tithe')->default(true);
            $table->decimal('amount', 12, 2)->nullable();
            $table->date('date')->nullable();
            $table->text('account_taken_from')->default('debit');
            $table->boolean('automatic_payment')->default(false);
            $table->text('notes')->nullable();
            $table->boolean('paid')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->foreignIdFor(\App\Models\User::class, 'user_id')->references('id')->on('users');
            $table->foreignIdFor(\App\Models\Income::class, 'income_id')->nullable()->references('id')->on('incomes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
