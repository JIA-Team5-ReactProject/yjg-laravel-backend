<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SemesterMealType extends Model
{
    use HasFactory;

    protected $fillable = [
        'meal_type',
        'content',
        'price',
    ];

    public function restaurantSemester(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RestaurantSemester::class);
    }
}
