<?php

namespace App\Http\Controllers;

use App\Imports\RestaurantMenuImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\RestaurantMenu;
use Illuminate\Support\Facades\Log;

class RestaurantMenusController extends Controller
{
    public function import(Request $request)
    {
        $excel_file = $request->file('excel_file');
        $excel_file->store('excels');
        Excel::import(new RestaurantMenuImport, $excel_file);
        return response()->json(['message' => 'Excel data has been imported successfully'], 200);
    }
}
