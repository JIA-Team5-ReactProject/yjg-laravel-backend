<?php

namespace App\Imports;

use App\Models\RestaurantMenu;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Carbon\Carbon;



class RestaurantMenuImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection (Collection $rows)
    {       
        for($j = 1; $j < 6; $j++){
            $i = 5;
            $menu= "";
            for ($i ; $i < 25; $i++){
                if($i === 5) {
                    $date = Carbon::createFromTimestamp(($rows[$i][$j] - 25569) * 86400);
                }
                if($i === 6) {
                    $menu= "";
                    continue;
                }
                $menu = $menu . " " . $rows[$i][$j];
                
                if ($i % 6 === 0 ) {
                    switch($i){
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

                    $menuData =new RestaurantMenu([
                        'date' => $date,
                        'menu'   => $menu,
                        'meal_type' => $meal_type,
                    ]);
                    $menuData->save();
                    $menu = "";
                }
                
            }
        }
        return;
    }
  
}
