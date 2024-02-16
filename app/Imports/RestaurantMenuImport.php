<?php

namespace App\Imports;

use App\Models\RestaurantMenu;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;

class RestaurantMenuImport implements ToModel
{
    /**
    * @param Collection $collection
    */
    public function model (array $row)
    {
        
        return new RestaurantMenu([
            'date' => date('Y-m-d', strtotime($row[0])), // 날짜 값 파싱
            'meal_type'           => $row[1],
            'menu'                => $row[2],
        ]);
    }
}
