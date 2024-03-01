<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Http\Controllers\Admin\SalonBusinessHourController;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            SalonSeeder::class,
            SalonBusinessHourSeeder::class,
            MeetingRoomSeeder::class,
            UserSeeder::class,
            MeetingRoomReservationSeeder::class,
            NoticeSeeder::class,
            AfterServiceSeeder::class,
        ]);
    }
}
