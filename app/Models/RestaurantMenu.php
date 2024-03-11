<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantMenu extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'menu',
        'meal_time',
    ];
    public function restaurantMenuMonth() {
        $this->belongsTo(RestaurantMenuMonth::class);
    }
    
}
