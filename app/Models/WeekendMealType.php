<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeekendMealType extends Model
{
    use HasFactory;
    protected $fillable = [
        'meal_type',
        'date',
        'content',
        'price',
        
    ];


    // public function restaurantWeekendMealType(): \Illuminate\Database\Eloquent\Relations\HasMany
    // {
    //     return $this->hasMany(RestaurantWeekendMealType::class);
    // }

    public function restaurantWeekends()
    {
        return $this->belongsToMany(RestaurantWeekend::class, 'restaurant_weekend_meal_types')
                    ->withPivot('restaurant_weekend_id'); // 필요에 따라 추가적인 pivot 정보를 가져올 수 있습니다.
    }
}
