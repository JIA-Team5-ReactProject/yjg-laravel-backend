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
        Schema::create('restaurant_weekend_autos', function (Blueprint $table) {
            $table->id();
            $table->string('start_week')->default(0);
            $table->string('end_week')->default(0);
            $table->string('start_time')->default("00:00");
            $table->string('end_time')->default("00:00");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_weekend_autos');
    }
};