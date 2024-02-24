<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantSemesterMealType extends Model
{
    use HasFactory;

    protected $fillable = [
        'semester_meal_type_id',
        'restaurant_semester_id',
    ];
    public function semester_meal_type() {
        $this->belongsTo(SemesterMealType::class);
    }

    public function restaurant_semesters() {
        $this->belongsTo(RestaurantSemester::class);
    }
}
