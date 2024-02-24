<?php

namespace Database\Seeders;

use App\Models\SalonBusinessHour;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SalonBusinessHourSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dayList = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];

        foreach ($dayList as $day) {
            SalonBusinessHour::create([
                's_time' => date('H:i', strtotime('09:00')),
                'e_time' => date('H:i', strtotime('18:00')),
                'date'   => $day,
            ]);
        }
    }
}
