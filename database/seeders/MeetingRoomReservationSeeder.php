<?php

namespace Database\Seeders;

use App\Models\MeetingRoomReservation;
use Illuminate\Database\Seeder;

class MeetingRoomReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MeetingRoomReservation::factory()->count(10)->create();
    }
}
