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
        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_receipt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('maintenance_bill_id')->constrained()->cascadeOnDelete();
            $table->decimal('allocated_amount', 12, 2);
            $table->timestamps();

            $table->unique(['payment_receipt_id', 'maintenance_bill_id'], 'payment_allocations_receipt_bill_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
    }
};
