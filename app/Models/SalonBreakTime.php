<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalonBreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        's_time',
        'e_time',
        'date',
    ];
}
