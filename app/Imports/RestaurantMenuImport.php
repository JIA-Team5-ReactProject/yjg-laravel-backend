<?php

namespace App\Imports;

use App\Models\RestaurantMenu;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Carbon\Carbon;
use Exception;

class RestaurantMenuImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection (Collection $rows)
    {
        try {
            for ($j = 1; $j < 6; $j++) {
                $i = 5;
                $menu = "";
                for ($i; $i < 25; $i++) {
                    if (!isset($rows[$i][$j])) {
                        // 셀이 비어있는 경우 무시
                        continue;
                    }
                    if ($i === 5) {
                        $date = Carbon::createFromTimestamp(($rows[$i][$j] - 25569) * 86400);
                    }
                    if ($i === 6) {
                        $menu = "";
                        continue;
                    }
                    $menu = $menu . " " . $rows[$i][$j];

                    if ($i % 6 === 0) {
                        switch ($i) {
                            case 12:
                                $meal_type = 'b';
                                break;
                            case 18:
                                $meal_type = 'l';
                                break;
                            case 24:
                                $meal_type = 'd';
                                break;
                            default:
                                $meal_type = "error";
                        }
                        //식단표 모델로 보내서 db에 저장
                        $menuData = new RestaurantMenu([
                            'date' => $date,
                            'menu' => $menu,
                            'meal_type' => $meal_type,
                        ]);
                        $menuData->save();
                        $menu = "";
                    }
                }
            }
        }catch(Exception $e){
            Log::error('Error during import: ' . $e->getMessage());
        }
        return;
    }
  
}
