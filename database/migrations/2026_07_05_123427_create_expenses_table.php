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
            $table->date('expense_month_year')->nullable();
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->date('date')->nullable();
            $table->string('fixed_or_variable')->nullable();
            $table->text('account_taken_from')->nullable();
            $table->string('manual_or_automatic')->nullable();
            $table->boolean('tithe')->default(false);
            $table->boolean('automatically_generated_tithe')->default(false); // can this be removed?
            $table->boolean('paid')->default(false);
            $table->text('notes')->nullable();
            $table->foreignIdFor(\App\Models\Income::class, 'income_id')->nullable()->references('id')->on('incomes');
            $table->unsignedInteger('position')->default(0);
            $table->foreignIdFor(\App\Models\User::class, 'user_id')->references('id')->on('users');
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
