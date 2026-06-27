<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('societies')) {
            return;
        }

        if (!Schema::hasColumn('societies', 'security_module')) {
            Schema::table('societies', function (Blueprint $table) {
                // Flag indicating whether the optional security module is enabled
                // for this society. True means unit/flat data is required.
                $table->boolean('security_module')->default(false);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('societies')) {
            return;
        }

        if (Schema::hasColumn('societies', 'security_module')) {
            Schema::table('societies', function (Blueprint $table) {
                $table->dropColumn('security_module');
            });
        }
    }
};
