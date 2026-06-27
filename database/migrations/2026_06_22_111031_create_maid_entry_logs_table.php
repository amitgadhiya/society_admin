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
        if (Schema::hasTable('maid_entry_logs')) { return; }
        Schema::create('maid_entry_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')
                ->constrained('societies')
                ->cascadeOnDelete();
            $table->foreignId('maid_id')
                ->constrained('maids')
                ->cascadeOnDelete();
            $table->foreignId('watchman_id')
                ->nullable()
                ->constrained('watchmen')
                ->nullOnDelete();
            $table->dateTime('enter_time')->useCurrent();
            $table->dateTime('exit_time')->nullable();
            $table->enum('status', ['enter', 'exit'])->default('enter');
            $table->timestamps();
            $table->index(['maid_id', 'status']);
            $table->index('enter_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maid_entry_logs');
    }
};
