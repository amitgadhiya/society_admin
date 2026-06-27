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
        Schema::table('visitors', function (Blueprint $table) {
            if (!Schema::hasColumn('visitors', 'visitor_code')) {
                $table->string('visitor_code', 50)->nullable()->after('id');
                $table->string('otp', 10)->nullable()->after('visitor_code');
                $table->timestamp('otp_expires_at')->nullable()->after('otp');
                $table->index('visitor_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            if (Schema::hasColumn('visitors', 'visitor_code')) {
                $table->dropIndex(['visitor_code']);
                $table->dropColumn(['visitor_code', 'otp', 'otp_expires_at']);
            }
        });
    }
};
