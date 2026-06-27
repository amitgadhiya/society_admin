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
        if (Schema::hasTable('watchman_task_logs')) { return; }
        Schema::create('watchman_task_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')
                ->constrained('tasks')
                ->cascadeOnDelete();
            $table->foreignId('watchman_id')
                ->constrained('watchmen')
                ->cascadeOnDelete();
            $table->date('completion_date');
            $table->boolean('is_completed')->default(false);
            $table->string('photo')->nullable();
            $table->decimal('latitude',10,8)->nullable();
            $table->decimal('longitude',10,8)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->unique([
                'task_id',
                'watchman_id',
                'completion_date'
            ], 'unique_daily_task_completion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watchman_task_logs');
    }
};
