<?php

namespace Database\Factories;

use App\Models\MeetingRoom;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MeetingRoomReservation>
 */
class MeetingRoomReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $randomTime = fake()->time();

        $carbonTime = Carbon::parse($randomTime);

        $carbonTime->startOfHour();

        $s_time = $carbonTime->toTimeString();

        do {
            $e_time = $carbonTime->addHours(random_int(1, 4))->toTimeString();
        } while ($e_time > Carbon::parse('00:00:00'));

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $randomDate = fake()->dateTimeBetween($startOfMonth, $endOfMonth)->format('Y-m-d');

        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'meeting_room_id' => MeetingRoom::inRandomOrder()->first()->id,
            'reservation_date' => $randomDate,
            'reservation_s_time' => $s_time,
            'reservation_e_time' => $e_time,
        ];
    }
}
