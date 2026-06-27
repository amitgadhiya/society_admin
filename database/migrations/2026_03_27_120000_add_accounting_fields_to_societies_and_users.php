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
        Schema::table('societies', function (Blueprint $table) {
            $table->string('address')->nullable()->after('code');
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('pincode', 20)->nullable()->after('state');
            $table->unsignedTinyInteger('billing_day')->default(1)->after('pincode');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('owner')->after('password');
            $table->string('status')->default('active')->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'status']);
        });

        Schema::table('societies', function (Blueprint $table) {
            $table->dropColumn(['address', 'city', 'state', 'pincode', 'billing_day']);
        });
    }
};
