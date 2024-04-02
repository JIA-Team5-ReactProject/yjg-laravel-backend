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
        Schema::create('restaurant_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('date_id')->constrained('restaurant_menu_dates')->cascadeOnUpdate()->cascadeOnDelete();
            $table->date('date');
            $table->string('menu')->nullable();
            $table->char('meal_time',1);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurant_menus', function (Blueprint $table) {
            $table->dropForeign(['restaurant_menu_dates']);
            // $table->dropIfExists();
        });
        Schema::dropIfExists('restaurant_menus');
    }
    
};
