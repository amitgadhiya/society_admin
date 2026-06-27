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
        if (Schema::hasTable('tasks')) { return; }
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
             $table->boolean('is_repetitive')->default(false);
            // Non-repetitive tasks
            $table->date('deadline_date')->nullable();
            $table->time('scheduled_time')->nullable();
            // Repetitive tasks
            $table->unsignedSmallInteger('days_to_complete')->nullable();
            $table->enum('recurrence_type', [
                'daily', 'weekly', 'monthly', 'quarterly', 'biannual', 'annual',
            ])->nullable();
            // Recurrence end condition
            $table->enum('recurrence_ends', ['never', 'after_occurrences', 'on_date'])->nullable()->default('never');
            $table->unsignedSmallInteger('occurrences')->nullable();
            $table->date('end_date')->nullable();
            // Weekly: array of day numbers [1=Mon … 7=Sun]
            $table->json('week_days')->nullable();
            // Monthly: which day of the month (1–31) + optional month filter
            $table->unsignedTinyInteger('month_day')->nullable();
            $table->json('months')->nullable(); // [1,3,6,9] = Jan,Mar,Jun,Sep; null = all months
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
