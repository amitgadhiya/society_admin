<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('watchman_notifications')) { return; }
        Schema::create('watchman_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('watchman_id')->constrained('watchmen')->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->string('type')->default('general');
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['watchman_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watchman_notifications');
    }
};
