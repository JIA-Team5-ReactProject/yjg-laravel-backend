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


    public function restaurantWeekendMealType(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RestaurantWeekendMealType::class);
    }
}
