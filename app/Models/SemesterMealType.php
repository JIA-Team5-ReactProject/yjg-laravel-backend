<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SemesterMealType extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'content',
        'price',
    ];

    public function restaurantSemesterMealType(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RestaurantSemesterMealType::class);
    }
}
