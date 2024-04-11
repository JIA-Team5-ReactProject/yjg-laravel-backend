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
    

    // Accessor : 모델에서 특정 속성에 대한 값을 포맷하거나 변환할 때 사용함
    //  public function getSatAttribute($value)
    // {
    //     return $value == 1 ? '토요일' : ''; 
    // }

    // public function getSunAttribute($value)
    // {
    //     return $value == 1 ? '일요일' : ''; 
    // }
    
    
}
