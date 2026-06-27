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
        if (!Schema::hasColumn('payment_receipts', 'opening_balance_applied')) {
            Schema::table('payment_receipts', function (Blueprint $table) {
                $table->decimal('opening_balance_applied', 12, 2)->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_receipts', function (Blueprint $table) {
            $table->dropColumn('opening_balance_applied');
        });
    }
};
