<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantWeekendMealType extends Model
{
    use HasFactory;
    protected $fillable = [
        'weekend_meal_type_id',
        'restaurant_weekend_id',
    ];

    public function weekend_meal_types() {
        $this->belongsTo(WeekendMealType::class);
    }
    

    public function restaurant_weekends() {
        $this->belongsTo(RestaurantWeekend::class);
    }
}
