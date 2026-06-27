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
        Schema::create('maintenance_bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_bill_id')->constrained()->cascadeOnDelete();
            $table->string('charge_name');
            $table->string('charge_code')->nullable();
            $table->decimal('amount', 12, 2);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_bill_items');
    }
};
