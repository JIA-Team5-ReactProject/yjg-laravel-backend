<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestaurantWeekend extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment',
        'refund',
        'sat',
        'sun',
    ];
    public function user() {
       return $this->belongsTo(User::class);
    }
    // public function restaurantWeekendMealType(): \Illuminate\Database\Eloquent\Relations\HasMany
    // {
    //     return $this->hasMany(RestaurantWeekendMealType::class);
    // }

    public function WeekendMealType()
    {
        return $this->belongsToMany(WeekendMealType::class, 'restaurant_weekend_meal_types')
                    ->withPivot('weekend_meal_type_id'); 
    }
    
}
