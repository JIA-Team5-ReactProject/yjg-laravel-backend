<?php

namespace App\Http\Controllers;

use App\Models\BusRoute;
use App\Models\BusSchedule;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class busScheduleController extends Controller
{
    public function store(Request $request)
    {
        try {
            // 유효성 검사
            $validatedData = $request->validate([
                'weekend' => 'required|boolean',
                'semester' => 'required|boolean',
                'bus_route_direction' => 'required|string|size:1',
                'station' => 'required|string',
                'bus_time' => 'required|date_format:H:i'
            ]);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }


        try{
            $busRoute = BusRoute::where('weekend', $validatedData['weekend'])
                             ->where('semester', $validatedData['semester'])
                             ->where('bus_route_direction', $validatedData['bus_route_direction'])
                             ->firstOrFail(); // 일치하는 항목이 없으면 예외 발생
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 404);
        }   

        
        try{
            BusSchedule::create([
                'bus_route_id' => $busRoute->id,
                'station' => $validatedData['station'],
                'bus_time' => $validatedData['bus_time'],
            ]);
        }catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }
}
