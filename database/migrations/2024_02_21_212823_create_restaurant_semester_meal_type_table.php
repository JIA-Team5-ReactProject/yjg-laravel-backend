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
        Schema::create('restaurant_semester_meal_type', function (Blueprint $table) {
            $table->id();
            $table->char('semester_meal_type_id', 1);
            $table->foreign('semester_meal_type_id')->references('id')->on('semester_meal_type')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('restaurant_semester_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurant_semester_meal_type', function (Blueprint $table) {
            $table->dropForeign(['restaurant_semester_id']);
            $table->dropForeign(['semester_meal_type_id']);
            $table->dropIfExists();
        });
    }
};
