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
        'weekend_meal_type_id',
        'payment',
        'refund',
        'sat',
        'sun',
    ];
    public function user() {
       return $this->belongsTo(User::class);
    }

    public function weekendMealType() {
        return $this->belongsTo(WeekendMealType::class);
     }
    

    
    
}
