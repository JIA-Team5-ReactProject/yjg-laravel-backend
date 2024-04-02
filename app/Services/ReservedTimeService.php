<?php

namespace App\Services;

use App\Models\MeetingRoomReservation;
use Carbon\Carbon;

class ReservedTimeService
{
    private string $date;
    private string $roomNumber;
    public function __construct(string $date, string $roomNumber) {
        $this->date = $date;
        $this->roomNumber = $roomNumber;
    }

    /**
     * 파라미터로 받은 reservation_date, meeting_room_number와 일치하는 예약을 찾아,
     * 한 시간 단위로 나누어 배열에 담아 반환
     * @return array
     */
    public function __invoke(): array
    {
        $reservations = MeetingRoomReservation::where('reservation_date', $this->date)->where('meeting_room_number' , $this->roomNumber)->where('status', true)->get();

        $reservedTimes = [];

        foreach ($reservations as $reservation) {
            $start = Carbon::parse($reservation->reservation_s_time);
            $end   = Carbon::parse($reservation->reservation_e_time);
            $currentHour = $start->copy();
            while ($currentHour <= $end) {
                $reservedTimes[] = $currentHour->copy()->format('H:i');
                $currentHour->addHour();
            }
        }

        sort($reservedTimes);

        return $reservedTimes;
    }
}
