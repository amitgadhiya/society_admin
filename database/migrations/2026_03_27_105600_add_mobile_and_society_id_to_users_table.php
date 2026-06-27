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
        Schema::table('users', function (Blueprint $table) {
            $table->string('mobile')->unique()->after('name');
            $table->foreignId('society_id')->nullable()->after('mobile')->constrained('societies')->nullOnDelete();
            $table->string('email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['society_id']);
            $table->dropColumn('society_id');
            $table->dropUnique('users_mobile_unique');
            $table->dropColumn('mobile');
            $table->string('email')->nullable(false)->change();
        });
    }
};
