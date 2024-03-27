<?php

namespace Database\Seeders;

use App\Models\MeetingRoomReservation;
use App\Models\User;
use Illuminate\Database\Seeder;

class MeetingRoomReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = '[
    {
        "reservation_date": "2024-03-18",
        "meeting_room_number": "203",
        "status": 1,
        "reservation_s_time": "11:00",
        "reservation_e_time": "14:00"
    },
    {
        "reservation_date": "2024-03-19",
        "meeting_room_number": "203",
        "status": 1,
        "reservation_s_time": "10:00",
        "reservation_e_time": "10:00"
    },
    {
        "reservation_date": "2024-03-20",
        "meeting_room_number": "203",
        "status": 1,
        "reservation_s_time": "15:00",
        "reservation_e_time": "15:00"
    },
    {
        "reservation_date": "2024-03-21",
        "meeting_room_number": "203",
        "status": 1,
        "reservation_s_time": "16:00",
        "reservation_e_time": "18:00"
    },
    {
        "reservation_date": "2024-03-22",
        "meeting_room_number": "203",
        "status": 1,
        "reservation_s_time": "20:00",
        "reservation_e_time": "21:00"
    },
    {
        "reservation_date": "2024-03-25",
        "meeting_room_number": "203",
        "status": 1,
        "reservation_s_time": "22:00",
        "reservation_e_time": "23:00"
    },
    {
        "reservation_date": "2024-03-26",
        "meeting_room_number": "204",
        "status": 1,
        "reservation_s_time": "14:00",
        "reservation_e_time": "23:00"
    },
    {
        "reservation_date": "2024-03-27",
        "meeting_room_number": "204",
        "status": 1,
        "reservation_s_time": "06:00",
        "reservation_e_time": "08:00"
    },
    {
        "reservation_date": "2024-03-28",
        "meeting_room_number": "204",
        "status": 1,
        "reservation_s_time": "22:00",
        "reservation_e_time": "22:00"
    },
    {
        "reservation_date": "2024-03-29",
        "meeting_room_number": "204",
        "status": 1,
        "reservation_s_time": "17:00",
        "reservation_e_time": "17:00"
    }
]';
        $reservations = json_decode($json, true);

        foreach ($reservations as $reservation) {
            $user = User::where('name', '엄준식')->first();
            $reservation['user_id'] = $user->id;
            MeetingRoomReservation::create($reservation);
        }
    }
}
