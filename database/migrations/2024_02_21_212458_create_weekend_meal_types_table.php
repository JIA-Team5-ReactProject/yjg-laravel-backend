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
        Schema::create('weekend_meal_types', function (Blueprint $table) {
            $table->id();
            $table->string('meal_type', 10);
            $table->string('content',50)->nullable();
            $table->string('price', 10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekend_meal_types');
    }
};
