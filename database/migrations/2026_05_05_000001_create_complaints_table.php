<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('complaints')) { return; }
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->timestamp('closed_on')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('open'); // open, in_progress, closed
            $table->string('before_image')->nullable();
            $table->string('after_image')->nullable();
            $table->text('remark_after_solution')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('closed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
