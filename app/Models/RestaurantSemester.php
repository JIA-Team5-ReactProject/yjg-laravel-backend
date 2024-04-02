<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestaurantSemester extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'semester_meal_type_id',
        'payment',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }


    public function SemesterMealType()
    {
        return $this->belongsTo(SemesterMealType::class);
    }

   
}
