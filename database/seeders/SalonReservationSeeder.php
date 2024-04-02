<?php

namespace Database\Seeders;

use App\Models\SalonReservation;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SalonReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = '[
    {
        "salon_service_id": 1,
        "reservation_date": "2024-03-20",
        "reservation_time": "10:00",
        "status": "confirm"
    },
    {
        "salon_service_id": 20,
        "reservation_date": "2024-03-22",
        "reservation_time": "12:00",
        "status": "submit"
    },
    {
        "salon_service_id": 24,
        "reservation_date": "2024-03-21",
        "reservation_time": "11:00",
        "status": "confirm"
    },
    {
        "salon_service_id": 14,
        "reservation_date": "2024-03-21",
        "reservation_time": "14:00",
        "status": "submit"
    },
    {
        "salon_service_id": 24,
        "reservation_date": "2024-03-21",
        "reservation_time": "14:00",
        "status": "confirm"
    },
    {
        "salon_service_id": 22,
        "reservation_date": "2024-03-21",
        "reservation_time": "14:00",
        "status": "reject"
    },
    {
        "salon_service_id": 15,
        "reservation_date": "2024-03-21",
        "reservation_time": "12:00",
        "status": "submit"
    }
]';
        $reservations = json_decode($json, true);

        foreach ($reservations as $reservation) {
            $user = User::where('name', '엄준식')->first();
            $reservation['user_id'] = $user->id;
            SalonReservation::create($reservation);
        }
    }
}
