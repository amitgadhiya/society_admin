<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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

        $hasOld = Schema::hasColumn('societies', 'flat_required');
        $hasNew = Schema::hasColumn('societies', 'security_module');

        // If old column exists and new doesn't, rename it (MySQL raw SQL fallback
        // avoids requiring doctrine/dbal for renames).
        if ($hasOld && !$hasNew) {
            try {
                DB::statement("ALTER TABLE `societies` CHANGE `flat_required` `security_module` TINYINT(1) NOT NULL DEFAULT 0");
            } catch (\Throwable $e) {
                // Best-effort fallback: attempt Schema rename (may require doctrine/dbal).
                Schema::table('societies', function (Blueprint $table) {
                    if (Schema::hasColumn('societies', 'flat_required')) {
                        $table->renameColumn('flat_required', 'security_module');
                    }
                });
            }
            return;
        }

        // If neither column exists, create the new `security_module` boolean.
        if (!$hasOld && !$hasNew) {
            Schema::table('societies', function (Blueprint $table) {
                $table->boolean('security_module')->default(false);
            });
        }

        // If both exist (unlikely), drop the old one to keep schema clean.
        if ($hasOld && $hasNew) {
            Schema::table('societies', function (Blueprint $table) {
                if (Schema::hasColumn('societies', 'flat_required')) {
                    $table->dropColumn('flat_required');
                }
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

        $hasOld = Schema::hasColumn('societies', 'flat_required');
        $hasNew = Schema::hasColumn('societies', 'security_module');

        if ($hasNew && !$hasOld) {
            try {
                DB::statement("ALTER TABLE `societies` CHANGE `security_module` `flat_required` TINYINT(1) NOT NULL DEFAULT 0");
            } catch (\Throwable $e) {
                Schema::table('societies', function (Blueprint $table) {
                    if (Schema::hasColumn('societies', 'security_module')) {
                        $table->renameColumn('security_module', 'flat_required');
                    }
                });
            }
            return;
        }

        if (!$hasNew && !$hasOld) {
            Schema::table('societies', function (Blueprint $table) {
                $table->boolean('flat_required')->default(false);
            });
        }

        if ($hasOld && $hasNew) {
            Schema::table('societies', function (Blueprint $table) {
                if (Schema::hasColumn('societies', 'security_module')) {
                    $table->dropColumn('security_module');
                }
            });
        }
    }
};
