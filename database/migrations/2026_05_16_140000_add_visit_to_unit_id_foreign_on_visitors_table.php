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
        if (!Schema::hasTable('visitors')) {
            return;
        }

        // Add a nullable foreign key column to reference units.id without dropping existing data.
        Schema::table('visitors', function (Blueprint $table) {
            if (!Schema::hasColumn('visitors', 'visit_to_unit_id')) {
                $table->unsignedBigInteger('visit_to_unit_id')->nullable()->after('visit_to');
                $table->foreign('visit_to_unit_id')->references('id')->on('units')->onDelete('set null');
            }
        });

        // Backfill visit_to_unit_id by mapping existing visit_to values to units.
        if (Schema::hasTable('units')) {
            // Process in PHP to keep DB compatibility.
            $visitors = DB::table('visitors')->select('id', 'visit_to')->whereNotNull('visit_to')->get();
            foreach ($visitors as $v) {
                if ($v->visit_to === null) {
                    continue;
                }

                // Try matching by unit_number first (common case where visit_to stores unit_number)
                $unit = DB::table('units')->where('unit_number', $v->visit_to)->first();

                // If that fails and visit_to looks numeric, try matching by id
                if (!$unit && is_numeric($v->visit_to)) {
                    $unit = DB::table('units')->where('id', intval($v->visit_to))->first();
                }

                if ($unit) {
                    DB::table('visitors')->where('id', $v->id)->update(['visit_to_unit_id' => $unit->id]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('visitors')) {
            return;
        }

        Schema::table('visitors', function (Blueprint $table) {
            if (Schema::hasColumn('visitors', 'visit_to_unit_id')) {
                $table->dropForeign(['visit_to_unit_id']);
                $table->dropColumn('visit_to_unit_id');
            }
        });
    }
};
