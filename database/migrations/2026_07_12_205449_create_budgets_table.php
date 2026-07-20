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
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->decimal('current_balance', 12, 2)->nullable();
            $table->text('frequency')->nullable();
            $table->text('date_available')->nullable();
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
        Schema::dropIfExists('budgets');
    }
};
