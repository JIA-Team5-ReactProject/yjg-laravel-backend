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
        Schema::create('restaurant_semester_meal_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_meal_type_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('restaurant_semester_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurant_semester_meal_types', function (Blueprint $table) {
            $table->dropForeign(['restaurant_semester_id']);
            $table->dropForeign(['semester_meal_type_id']);
            // $table->dropIfExists();
        });
        Schema::dropIfExists('restaurant_semester_meal_types');
    }
};
