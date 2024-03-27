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
        Schema::create('restaurant_weekends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('weekend_meal_type_id')->constrained('weekend_meal_types')->cascadeOnUpdate()->cascadeOnDelete();
            $table->boolean('payment')->default(false);
            $table->boolean('refund')->default(false);
            $table->boolean('sat')->default(false);
            $table->boolean('sun')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurant_weekends', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['weekend_meal_type_id']);
            $table->dropIfExists();
        });
    }
};
