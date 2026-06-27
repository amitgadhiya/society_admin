<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('visitors', 'unit_id')) {
                $table->foreignId('unit_id')->nullable()->constrained('units')->cascadeOnDelete();
            }
            
            if (!Schema::hasColumn('visitors', 'status')) {
                $table->enum('status', ['pending', 'allowed', 'not_allowed'])->default('pending')->after('reason');
            }
            
            if (!Schema::hasColumn('visitors', 'remarks')) {
                $table->text('remarks')->nullable()->after('status');
            }
            
            if (!Schema::hasColumn('visitors', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('remarks');
            }
        });

        // Migrate data from permission_granted to status
        DB::table('visitors')->whereRaw('permission_granted = 1')->update(['status' => 'allowed']);
        DB::table('visitors')->whereRaw('permission_granted = 0')->update(['status' => 'pending']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->dropColumn(['unit_id', 'status', 'remarks', 'rejection_reason']);
        });
    }
};

