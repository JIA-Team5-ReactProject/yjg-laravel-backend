<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantMenuDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'month',
        'year',
        'week'
    ];

    public function restaurant_menu(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RestaurantMenu::class);
    }
}
