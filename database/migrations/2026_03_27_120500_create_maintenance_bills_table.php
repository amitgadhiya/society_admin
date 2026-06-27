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
        Schema::create('maintenance_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained()->cascadeOnDelete();
            $table->foreignId('billing_cycle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->string('bill_no')->unique();
            $table->date('bill_date');
            $table->date('due_date');
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->decimal('total_charges', 12, 2)->default(0);
            $table->decimal('total_discount', 12, 2)->default(0);
            $table->decimal('late_fee', 12, 2)->default(0);
            $table->decimal('total_paid', 12, 2)->default(0);
            $table->decimal('closing_balance', 12, 2)->default(0);
            $table->string('status')->default('unpaid');
            $table->timestamps();

            $table->unique(['billing_cycle_id', 'unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_bills');
    }
};
