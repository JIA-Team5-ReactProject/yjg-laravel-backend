<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantMealType extends Model
{
    use HasFactory;

    protected $fillable = [
        'meal_type',
        'meal_genre',
        'content',
        'price',
        'weekend',
    ];
    public function restaurant_menus() {
        $this->belongsTo(RestaurantMenu::class);
    }
}
