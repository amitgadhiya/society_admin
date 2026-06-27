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
        if (Schema::hasColumn('units', 'maintenance_scheme')) {
            Schema::table('units', function (Blueprint $table) {
                $table->dropColumn('maintenance_scheme');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->string('maintenance_scheme', 255)->nullable()->after('area_sqft');
        });
    }
};
