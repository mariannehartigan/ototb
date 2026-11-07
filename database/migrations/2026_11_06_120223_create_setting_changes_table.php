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
        Schema::create('setting_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Setting::class, 'setting_id')->references('id')->on('settings');
            $table->boolean('tithe')->default(true);
            $table->boolean('use_budget')->default(false);
            $table->string('budget_day_of_week')->nullable();
            $table->boolean('save_for_five_budget_months');            $table->date('last_date_before_change')->default(now())->nullable();
            $table->date('first_date_of_change')->default(now())->nullable();    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings_changes');
    }
};
