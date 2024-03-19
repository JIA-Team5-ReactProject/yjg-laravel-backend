<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantApplySetting extends Model
{
    use HasFactory;
    protected $fillable = [
        'semester_open',

        'start_week',
        'end_week',
        'start_time',
        'end_time',
    ];

}
