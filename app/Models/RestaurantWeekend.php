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
    ];
    public function user() {
        $this->belongsTo(User::class);
    }
    public function restaurantWeekendMealType(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RestaurantWeekendMealType::class);
    }
}
