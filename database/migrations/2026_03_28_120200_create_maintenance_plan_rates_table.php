<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_plan_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_plan_id')->constrained()->cascadeOnDelete();
            $table->string('unit_type');
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->unique(['maintenance_plan_id', 'unit_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_plan_rates');
    }
};
