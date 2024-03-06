<?php

namespace App\Models;

use App\Casts\TimeCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalonReservation extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'salon_service_id',
        'user_id',
        'reservation_time',
        'reservation_date',
        'status',
    ];

    protected $casts = [
        'reservation_time' => TimeCast::class,
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function salonService() {
        return $this->belongsTo(SalonService::class);
    }

}
