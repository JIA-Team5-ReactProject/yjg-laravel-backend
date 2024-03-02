<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function meetingRoom(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MeetingRoom::class);
    }
}
