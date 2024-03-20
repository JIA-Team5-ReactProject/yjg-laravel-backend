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
        'payment',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
    public function restaurantSemesterMealType(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RestaurantSemesterMealType::class);
    }

    public function SemesterMealType()
    {
        return $this->belongsToMany(SemesterMealType::class, 'restaurant_semester_meal_types')
                    ->withPivot('semester_meal_type_id');
    }

   
}
