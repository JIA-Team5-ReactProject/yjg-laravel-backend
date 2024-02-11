<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalonPrice extends Model
{
    use HasFactory;

    protected $fillable = [
      'salon_service_id',
      'gender',
      'price',
    ];

    public function salonService(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SalonService::class);
    }

    public function salonReservations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SalonReservation::class);
    }
}
