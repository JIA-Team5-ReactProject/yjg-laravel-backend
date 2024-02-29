<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusRound extends Model
{
    use HasFactory;

    protected $fillable = [
        'round',
    ];

    public function bus_schedule(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BusSchedule::class);
    }
}
