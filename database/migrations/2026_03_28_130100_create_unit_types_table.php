<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('status')->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['society_id', 'name']);
            $table->index(['society_id', 'status']);
        });

        $rows = DB::table('units')
            ->select('society_id', 'unit_type')
            ->whereNotNull('unit_type')
            ->where('unit_type', '!=', '')
            ->distinct()
            ->get();

        foreach ($rows as $row) {
            DB::table('unit_types')->updateOrInsert(
                [
                    'society_id' => $row->society_id,
                    'name' => trim((string) $row->unit_type),
                ],
                [
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_types');
    }
};
