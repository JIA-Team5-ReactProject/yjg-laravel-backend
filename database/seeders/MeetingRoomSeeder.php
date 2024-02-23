<?php

namespace Database\Seeders;

use App\Models\MeetingRoom;
use App\Models\MeetingRoomReservation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MeetingRoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($roomN = 203; $roomN <= 209; $roomN++) {
            if($roomN != 207) {
                $meetingRoom = new MeetingRoom();
                $meetingRoom->room_number = $roomN;
                $meetingRoom->save();
            }
        }
    }
}
