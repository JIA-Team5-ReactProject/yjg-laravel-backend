<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantWeekendAuto extends Model
{
    use HasFactory;
    protected $fillable = [
        'start_week',
        'end_week',
        'start_time',
        'end_time',
        'state'
    ];
}
