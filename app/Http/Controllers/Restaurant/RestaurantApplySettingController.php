<?php

namespace App\Http\Controllers\Restaurant;

use App\Models\RestaurantApplySetting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class RestaurantApplySettingController extends Controller
{
    public function store()
    {
      RestaurantApplySetting::create([]);
      return response()->json(['message' => '식수 신청 날짜 셋팅 완료되었습니다.']); 
    }


    public function update(Request $request)
    {
      try {
        // 유효성 검사
        $validatedData = $request->validate([
            'start_week' => 'required|string',
            'end_week' => 'required|string',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
        ]);
      }catch (ValidationException $exception) {
        return response()->json(['error' => $exception->getMessage()], 422);
      }

      //어차피 1 하나밖에 없음
      $apply = RestaurantApplySetting::findOrFail(1);

      try{
        $apply->update([
          'semester_open' => $validatedData['semester_open'],
          'start_week' => $validatedData['start_week'],
          'end_week' => $validatedData['end_week'],
          'start_time' => $validatedData['start_time'],
          'end_time' => $validatedData['end_time'],
        ]);
        return response()->json(['message' => '식수신청 시간 설정이 완료되었습니다.']); 
      }catch (ValidationException $exception) {
        return response()->json(['error' => $exception->getMessage()], 422);
      }
    }

    public function semesterApply()
    {
      try{
        $semester_open = RestaurantApplySetting::pluck('semester_open');
        
        return response()->json(['semester_open' => $semester_open]);
      }catch (ValidationException $exception) {
        return response()->json(['error' => $exception->getMessage()], 422);
      }
    }

    public function weekendApply()
    {
      try{
        $now = Carbon::now();
        $date = RestaurantApplySetting::findOrFail(1);
        $start_time = $date->start_time;
        $end_time = $date->end_time;

        if ($now->between($start_time, $end_time)) {
          $result = true;
      } else {
          $result = false;
      }
        return response()->json(['semester_open' => $result]);
      }catch (ValidationException $exception) {
        return response()->json(['error' => $exception->getMessage()], 422);
      }
    }
}
