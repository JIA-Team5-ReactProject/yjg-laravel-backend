<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingRoom extends Model
{
    use HasFactory;
    protected $primaryKey = 'room_number';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'room_number',
    ];

    public function meetingRoomReservations(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(MeetingRoomReservation::class, 'meeting_room_number', 'room_number');
    }
}
