<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WeekendApplyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'payment' => $this->payment,
            'refund' => $this->refund,
            // 'user' 객체 대신 직접 통합
            'phone_number' => optional($this->user)->phone_number,
            'name' => optional($this->user)->name,
            'student_id' => optional($this->user)->student_id,
            // 'restaurant_meal_type'
            'meal_type' => optional($this->weekend_meal_type)->meal_type,
            
            'date' => optional($this->weekend_meal_type)->date,
        ];
    }
}
