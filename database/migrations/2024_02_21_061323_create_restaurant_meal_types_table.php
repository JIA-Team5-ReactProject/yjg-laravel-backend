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
        Schema::create('restaurant_meal_types', function (Blueprint $table) {
            $table->id();
            $table->char('meal_type',1);
            $table->char('meal_genre',1);
            $table->string('content')->nullable();
            $table->integer('price');
            $table->boolean('weekend')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_meal_types');
    }
};
