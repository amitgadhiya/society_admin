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
        if (!Schema::hasTable('watchmen')) {
            Schema::create('watchmen', function (Blueprint $table) {
                $table->id();
                $table->foreignId('society_id')->nullable()->constrained()->onDelete('cascade');
                $table->string('name');
                $table->string('mobile')->nullable();
                $table->string('password')->nullable();
                $table->string('photo')->nullable();
                $table->string('employee_id')->nullable();
                $table->string('fcm_token')->nullable();
                $table->boolean('active')->default(true);
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
        Schema::dropIfExists('watchmen');
    }
};
