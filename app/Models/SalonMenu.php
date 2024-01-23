<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalonMenu extends Model
{
    use HasFactory;
    protected $fillable = [
        'service',
        'price',
    ];


    public function salonReservation(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(SalonReservation::class);
    }
}
