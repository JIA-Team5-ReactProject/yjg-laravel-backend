<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\RestaurantApplyAuto;
use App\Models\RestaurantApplyDivision;
use App\Models\RestaurantApplyManual;
use App\Models\RestaurantSemesterAuto;
use App\Models\RestaurantWeekendAuto;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RestaurantApplyDivisionController extends Controller
{
    public function weekendAutoOn()
    {
        RestaurantWeekendAuto::create([]);
        return response()->json(['message' => '주말 식수 신청 날짜 셋팅 완료되었습니다.']); 
    }

    public function weekendAutoSet(Request $request)
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
          $apply = RestaurantWeekendAuto::findOrFail(1);
    
          try{
            $apply->update([
              'start_week' => $validatedData['start_week'],
              'end_week' => $validatedData['end_week'],
              'start_time' => $validatedData['start_time'],
              'end_time' => $validatedData['end_time'],
            ]);
            return response()->json(['message' => '주말 식수 신청 시간 수정이 완료되었습니다.']); 
          }catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
          }
    }

    public function semesterAutoOn()//디폴트로 설정
    {
        $startDate = Carbon::create(null, 3, 1);
        $endDate = Carbon::create(null, 6, 20);

        RestaurantSemesterAuto::create([
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        return response()->json(['message' => '학기 식수 신청 날짜 셋팅 완료되었습니다.']); 
    }


    public function semesterAutoSet(Request $request)
        {
            try {
                // 유효성 검사
                $validatedData = $request->validate([
                    'start_date' => 'required|string',
                    'end_date' => 'required|string'
                ]);
            }catch (ValidationException $exception) {
                return response()->json(['error' => $exception->getMessage()], 422);
            }
        
            //어차피 1 하나밖에 없음
            $apply = RestaurantSemesterAuto::findOrFail(2);
        
            try{
                $apply->update([
                'start_date' => $validatedData['start_date'],
                'end_date' => $validatedData['end_date'],
                ]);
                return response()->json(['message' => '주말 식수 신청 시간 수정이 완료되었습니다.']); 
            }catch (ValidationException $exception) {
                return response()->json(['error' => $exception->getMessage()], 422);
            }
        }

    public function manualSet(Request $request)//디폴트로 들어가야하는게 학기,방학 2개임
    {
        RestaurantApplyManual::create([
            'division' => $request->division,
            'open' => $request->open
        ]);
        return response()->json(['message' => '학기 식수 신청 날짜 셋팅 완료되었습니다.']); 
    }



    public function manual(Request $request)
    {
        try {
            // 유효성 검사
            $validatedData = $request->validate([
                'division' => 'required|string|in:semester,weekend',
                'open' => 'required|boolean'
            ]);
        }catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }

        try{
            $apply = RestaurantApplyManual::where('division', $validatedData['division'])->firstOr();
            Log::info('어플리: ' . $apply);
            $apply->update([
                'open' => $validatedData['open']
            ]);
            return response()->json(['message' => $validatedData['division'].'식수 신청 시간 수정이 완료되었습니다.']);
        }catch (ValidationException $exception) {
                return response()->json(['error' => $exception->getMessage()], 422);
            }
    }

}
