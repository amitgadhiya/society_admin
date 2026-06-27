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
        if (Schema::hasTable('maid_unit_assignments')) { return; }
        Schema::create('maid_unit_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maid_id')->constrained('maids')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->enum('type', [
                'maid',
                'cook',
                'driver',
                'nanny',
                'babysitter',
                'cleaner',
                'others'
            ]);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->enum('is_permitted', ['pending', 'allowed','not_allowed'])->default('pending');
            $table->timestamps();
            $table->unique(['maid_id', 'unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maid_unit_assignments');
    }
};
