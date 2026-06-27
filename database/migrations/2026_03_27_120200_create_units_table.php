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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wing_id')->nullable()->constrained('wings')->nullOnDelete();
            $table->string('unit_number');
            $table->unsignedInteger('floor')->nullable();
            $table->string('unit_type')->nullable();
            $table->decimal('area_sqft', 10, 2)->nullable();
            $table->string('maintenance_scheme')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['society_id', 'unit_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
