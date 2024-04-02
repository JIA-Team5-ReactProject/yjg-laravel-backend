<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeekendMealType extends Model
{
    use HasFactory;
    protected $fillable = [
        'meal_type',
        'content',
        'price',
    ];


    public function restaurantWeekend(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RestaurantWeekend::class);
    }
    
    
}
