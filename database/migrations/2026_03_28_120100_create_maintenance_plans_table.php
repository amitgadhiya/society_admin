<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained()->cascadeOnDelete();
            $table->string('mode')->default('same_for_all'); // same_for_all | by_unit_type
            $table->decimal('default_amount', 12, 2)->nullable();
            $table->date('effective_from');
            $table->string('status')->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['society_id', 'effective_from']);
            $table->index(['society_id', 'effective_from']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_plans');
    }
};
