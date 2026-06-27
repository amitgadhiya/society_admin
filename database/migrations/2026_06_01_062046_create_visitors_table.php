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
        if (Schema::hasTable('visitors')) { return; }
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            
            // Society reference
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            
            // Visitor Information
            $table->string('visitor_name');
            $table->string('photo')->nullable(); // Photo path
            $table->string('mobile')->nullable(); // Visitor phone number
            $table->string('vehicle_number')->nullable(); // Vehicle details
            $table->string('id_proof')->nullable(); // ID proof photo path
            
            // Visit Details
            $table->dateTime('in_at'); // Check-in time
            $table->dateTime('out_at')->nullable(); // Check-out time
            $table->foreignId('visit_to_unit_id')->nullable()->constrained('units')->cascadeOnDelete(); // Unit being visited
            $table->string('reason')->nullable(); // Purpose of visit
            
            // Status & Permission
            $table->enum('status', ['pending', 'allowed', 'not_allowed'])->default('pending');
            $table->text('remarks')->nullable(); // Additional remarks/notes
            $table->text('rejection_reason')->nullable(); // Reason if status is not_allowed
            
            // Added By (Two options: Watchman OR Unit resident)
            $table->foreignId('watchman_id')->nullable()->constrained('watchmen')->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->cascadeOnDelete(); // Unit resident who added this
            
            // Created by (User who created this record)
            $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnDelete();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index(['society_id', 'status']);
            $table->index('in_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
