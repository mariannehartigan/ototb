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
            $table->date('start_date')->default(now())->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('tithe')->default(false);
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
