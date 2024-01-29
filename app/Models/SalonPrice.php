<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalonPrice extends Model
{
    use HasFactory;

    protected $fillable = [
      'gender',
      'price',
    ];

    public function salonService(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('salon_services');
    }

    public function salonReservations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('salon_reservations');
    }
}
