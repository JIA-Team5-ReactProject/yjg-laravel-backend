<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantMenuMonth extends Model
{
    use HasFactory;

    protected $fillable = [
        'month'
    ];

    public function restaurant_menu(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RestaurantMenu::class);
    }
}
