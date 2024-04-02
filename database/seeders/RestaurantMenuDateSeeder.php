<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RestaurantMenuDateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 현재 날짜부터 시작
        $date = new \DateTime();
        
        // 3년 후 날짜 계산
        $endDate = (new \DateTime())->modify('+3 years');

        // 한 달 간격으로 루프
        while ($date <= $endDate) {
            for ($week = 1; $week <= 5; $week++) {
                DB::table('restaurant_menu_dates')->insert([
                    'year' => $date->format('Y'),
                    'month' => $date->format('m'),
                    'week' => (string)$week,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            // 다음 달로 이동
            $date->modify('+1 month');
        }
    }
}
