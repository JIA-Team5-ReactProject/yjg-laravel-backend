<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class busRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'weekend',
        'bus_route_direction'
    ];

    public function bus_times(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BusTime::class);
    }
}
