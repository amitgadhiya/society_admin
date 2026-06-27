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
        if (!Schema::hasTable('visitors')) {
            Schema::create('visitors', function (Blueprint $table) {
                $table->id();
                $table->foreignId('society_id')->nullable()->constrained()->onDelete('cascade');
                $table->string('visitor_name');
                $table->string('photo')->nullable();
                $table->string('mobile')->nullable();
                $table->timestamp('in_at')->nullable();
                $table->timestamp('out_at')->nullable();
                $table->string('visit_to')->nullable();
                $table->foreignId('watchman_id')->nullable()->constrained('watchmen')->onDelete('set null');
                $table->text('reason')->nullable();
                $table->boolean('permission_granted')->default(false);
                $table->string('vehicle_number')->nullable();
                $table->string('id_proof')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
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
        Schema::dropIfExists('visitors');
    }
};
