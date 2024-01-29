<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalonReservation extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'reservation_date',
        'status',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function salonPrice() {
        return $this->belongsTo('salon_prices');
    }

}
