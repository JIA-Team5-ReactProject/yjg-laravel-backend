<?php

namespace App\Models;

use App\Casts\TimeCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalonBusinessHour extends Model
{
    use HasFactory;

    protected $fillable = [
        's_time',
        'e_time',
        'date',
    ];

    protected $casts = [
        's_time' => TimeCast::class,
        'e_time' => TimeCast::class,
    ];
}
