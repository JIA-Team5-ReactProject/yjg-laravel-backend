<?php

namespace Database\Seeders;

use App\Models\BusRound;
use Illuminate\Database\Seeder;

class BusRoundSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = '[
    {
        "bus_route_id": 3,
        "round": "1회차(학교)"
    },
    {
        "bus_route_id": 3,
        "round": "2회차(학교)"
    },
    {
        "bus_route_id": 3,
        "round": "3회차(임대)"
    },
    {
        "bus_route_id": 3,
        "round": "4회차(학교)"
    },
    {
        "bus_route_id": 3,
        "round": "5회차(임대)"
    },
    {
        "bus_route_id": 3,
        "round": "6회차(학교)"
    },
    {
        "bus_route_id": 3,
        "round": "7회차(임대)"
    },
    {
        "bus_route_id": 3,
        "round": "8회차(임대)"
    },
    {
        "bus_route_id": 3,
        "round": "9회차(학교)"
    },
    {
        "bus_route_id": 3,
        "round": "10회차(학교)"
    },
    {
        "bus_route_id": 3,
        "round": "11회차(임대)"
    },
    {
        "bus_route_id": 4,
        "round": "1회차(임대)"
    },
    {
        "bus_route_id": 4,
        "round": "2회차(임대)"
    },
    {
        "bus_route_id": 4,
        "round": "3회차(학교)"
    },
    {
        "bus_route_id": 4,
        "round": "4회차(임대)"
    },
    {
        "bus_route_id": 4,
        "round": "5회차(학교)"
    },
    {
        "bus_route_id": 4,
        "round": "6회차(임대)"
    },
    {
        "bus_route_id": 4,
        "round": "7회차(학교, 칠곡운암)"
    },
    {
        "bus_route_id": 4,
        "round": "7회차(학교, 태전역)"
    },
    {
        "bus_route_id": 4,
        "round": "8회차(임대)"
    },
    {
        "bus_route_id": 4,
        "round": "9회차(학교)"
    },
    {
        "bus_route_id": 4,
        "round": "10회차(학교)"
    },
    {
        "bus_route_id": 1,
        "round": "1회차"
    },
    {
        "bus_route_id": 1,
        "round": "2회차"
    },
    {
        "bus_route_id": 1,
        "round": "3회차"
    },
    {
        "bus_route_id": 1,
        "round": "4회차"
    },
    {
        "bus_route_id": 2,
        "round": "1회차"
    },
    {
        "bus_route_id": 2,
        "round": "2회차"
    },
    {
        "bus_route_id": 2,
        "round": "3회차"
    },
    {
        "bus_route_id": 2,
        "round": "4회차"
    },
    {
        "bus_route_id": 7,
        "round": "1회차"
    },
    {
        "bus_route_id": 7,
        "round": "2회차"
    },
    {
        "bus_route_id": 7,
        "round": "3회차"
    },
    {
        "bus_route_id": 7,
        "round": "4회차"
    },
    {
        "bus_route_id": 7,
        "round": "5회차"
    },
    {
        "bus_route_id": 7,
        "round": "6회차"
    },
    {
        "bus_route_id": 8,
        "round": "1회차"
    },
    {
        "bus_route_id": 8,
        "round": "2회차"
    },
    {
        "bus_route_id": 8,
        "round": "3회차"
    },
    {
        "bus_route_id": 8,
        "round": "4회차"
    },
    {
        "bus_route_id": 8,
        "round": "5회차"
    },
    {
        "bus_route_id": 8,
        "round": "6회차"
    },
    {
        "bus_route_id": 6,
        "round": "1회차"
    },
    {
        "bus_route_id": 6,
        "round": "2회차"
    },
    {
        "bus_route_id": 6,
        "round": "3회차"
    },
    {
        "bus_route_id": 6,
        "round": "4회차"
    },
    {
        "bus_route_id": 5,
        "round": "1회차"
    },
    {
        "bus_route_id": 5,
        "round": "2회차"
    },
    {
        "bus_route_id": 5,
        "round": "3회차"
    },
    {
        "bus_route_id": 5,
        "round": "4회차"
    }
]';
        $rounds = json_decode($json, true);

        foreach ($rounds as $round) {
            BusRound::create($round);
        }
    }
}
