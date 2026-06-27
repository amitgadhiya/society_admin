<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->foreignId('unit_type_id')->nullable()->after('wing_id')->constrained('unit_types')->nullOnDelete();
        });

        $units = DB::table('units')
            ->select('id', 'society_id', 'unit_type')
            ->whereNotNull('unit_type')
            ->where('unit_type', '!=', '')
            ->get();

        foreach ($units as $unit) {
            $typeId = DB::table('unit_types')
                ->where('society_id', $unit->society_id)
                ->where('name', trim((string) $unit->unit_type))
                ->value('id');

            if ($typeId) {
                DB::table('units')
                    ->where('id', $unit->id)
                    ->update(['unit_type_id' => $typeId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_type_id');
        });
    }
};
