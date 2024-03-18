<?php

namespace App\Models;

use App\Casts\TimeCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingRoomReservation extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'meeting_room_number',
        'status',
        'reservation_date',
        'reservation_s_time',
        'reservation_e_time',
    ];

    protected $casts = [
        'reservation_s_time' => TimeCast::class,
        'reservation_e_time' => TimeCast::class,
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function meetingRoom(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MeetingRoom::class);
    }
}
