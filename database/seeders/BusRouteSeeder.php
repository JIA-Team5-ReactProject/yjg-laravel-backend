<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusRouteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $busRoutes = [
            ['weekend' => true, 'semester' => true, 'bus_route_direction' => 's_bokhyun'],
            ['weekend' => true, 'semester' => true, 'bus_route_direction' => 's_english'],
            ['weekend' => false, 'semester' => true, 'bus_route_direction' => 's_bokhyun'],
            ['weekend' => false, 'semester' => true, 'bus_route_direction' => 's_english'],
            ['weekend' => true, 'semester' => false, 'bus_route_direction' => 's_english'],
            ['weekend' => true, 'semester' => false, 'bus_route_direction' => 's_bokhyun'],
            ['weekend' => false, 'semester' => false, 'bus_route_direction' => 's_bokhyun'],
            ['weekend' => false, 'semester' => false, 'bus_route_direction' => 's_english'],
        ];

        // 배열을 사용하여 데이터베이스에 데이터 삽입
        foreach ($busRoutes as $route) {
            DB::table('bus_routes')->insert($route);
        }
    }
}
