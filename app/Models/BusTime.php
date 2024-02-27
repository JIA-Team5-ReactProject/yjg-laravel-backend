<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusTime extends Model
{
    use HasFactory;
    protected $fillable = [
            'bus_route_id',
            'bokhyun',
            'woobang',
            'city',
            'sk',
            'dc',
            'bukgu',
            'bank',
            'taejeon',
            'g_campus',
            'en',
            'munyang',
    ];
    public function bus_route() {
        $this->belongsTo(busRoute::class);
    }

}
