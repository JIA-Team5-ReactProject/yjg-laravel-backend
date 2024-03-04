<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'weekend',
        'bus_route_direction',
        'semester'
    ];


    public function bus_round(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BusSchedule::class);
    }
}
