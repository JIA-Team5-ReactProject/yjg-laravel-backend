<?php

use App\Models\RestaurantMenuDate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('restaurant_menu_dates', function (Blueprint $table) {
            $table->id();
            $table->string('year');
            $table->string('month');
            $table->string('week');
            $table->timestamps();
        });
        $yearUp = ((int) date('Y'));
        $years = [date('Y'), (string)$yearUp+1,(string)$yearUp+2,(string)$yearUp+3,(string)$yearUp+4,];
        

        $months = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
        $weeks = ['1', '2', '3', '4', '5'];

        foreach ($years as $year) {
            foreach ($months as $month) {
                foreach ($weeks as $week) {
                    DB::table('restaurant_menu_dates')->insert([
                        'year' => $year,
                        'month' => $month,
                        'week' => $week,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_menu_dates');
    }
};
