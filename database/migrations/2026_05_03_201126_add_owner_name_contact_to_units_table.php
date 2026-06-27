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
        Schema::table('units', function (Blueprint $table) {
            if (!Schema::hasColumn('units', 'registered_in_name_of')) {
                $table->string('registered_in_name_of', 255)->nullable();
            }
            if (!Schema::hasColumn('units', 'contact_number')) {
                $table->string('contact_number', 50)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn(['registered_in_name_of', 'contact_number']);
        });
    }
};
