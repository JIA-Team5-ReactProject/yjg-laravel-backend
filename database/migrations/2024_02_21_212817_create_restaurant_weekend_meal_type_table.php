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
        Schema::create('restaurant_weekend_meal_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weekend_meal_type_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('restaurant_weekend_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurant_weekend_meal_type', function (Blueprint $table) {
            $table->dropForeign(['restaurant_weekend_id']);
            $table->dropForeign(['weekend_meal_type_id']);
            $table->dropIfExists();
        });
    }
};
