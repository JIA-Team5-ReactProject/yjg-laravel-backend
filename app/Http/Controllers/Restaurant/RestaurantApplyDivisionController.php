<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\RestaurantApplyManual;
use App\Models\RestaurantApplyState;
use App\Models\RestaurantSemesterAuto;
use App\Models\RestaurantWeekend;
use App\Models\RestaurantWeekendAuto;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RestaurantApplyDivisionController extends Controller
{

    /**
     * @OA\Post (
     * path="/api/restaurant/apply/weekend/auto",
     * tags={"식수 신청 기간"},
     * summary="주말 식수 신청 날짜 디폴트 넣기",
     * description="주말 식수 신청 날짜 디폴트 넣기",
     *    
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function onWeekendAuto()
    {
        RestaurantWeekendAuto::create([]);
        return response()->json(['message' => '주말 식수 신청 날짜 셋팅 완료되었습니다.']); 
    }


    /**
     * @OA\Patch (
     * path="/api/restaurant/apply/weekend/set",
     * tags={"식수 신청 기간"},
     * summary="주말 식수 자동 신청 설정",
     * description="주말 식수 자동 신청 설정",
     *     @OA\RequestBody(
     *         description="설정할 시간",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="start_week", type="string", description="시작 요일(0~6)", example="2"),
     *                 @OA\Property (property="end_week", type="string", description="종료 요일(0~6)", example="5"),
     *                 @OA\Property (property="start_time", type="string", description="시작 시간", example="08:00"),
     *                 @OA\Property (property="end_time", type="string", description="종료 시간", example="22:00"),
     *                 @OA\Property (property="state", type="boolean", description="열림/닫힘", example=true),
     *             )
     *         )
     *     ),
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function setWeekendAuto(Request $request)
    {
        try {
            // 유효성 검사
            $validatedData = $request->validate([
                'start_week' => 'required|string',
                'end_week' => 'required|string',
                'start_time' => 'required|string',
                'end_time' => 'required|string',
                'state' => 'required|boolean'
            ]);
          }catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
          }
    
          //어차피 1 하나밖에 없음
          $apply = RestaurantWeekendAuto::first();
    
          try{
            $apply->update([
              'start_week' => $validatedData['start_week'],
              'end_week' => $validatedData['end_week'],
              'start_time' => $validatedData['start_time'],
              'end_time' => $validatedData['end_time'],
              'state' => $validatedData['state'],
            ]);

            $manual = RestaurantApplyManual::where('division', "weekend")->first();
            $manual->update([
                'state' => false
            ]);

            $state = RestaurantApplyState::first();
            $state->update([
                'weekend' => true,
                'semester' => false
            ]);


            return response()->json(['message' => '주말 식수 신청 시간 수정이 완료되었습니다.']); 
          }catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
          }
    }


    /**
     * @OA\Get (
     * path="/api/restaurant/apply/weekend/get",
     * tags={"식수 신청 기간"},
     * summary="방학 식수 자동 신청 get",
     * description="방학 식수 자동 신청 get",
     *    
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function getWeekendAuto()
    {
        try {
            $weekendAuto = RestaurantWeekendAuto::first();
            return response()->json(['semesterAuto' => $weekendAuto]);
        }catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }
    }


      /**
     * @OA\Get (
     * path="/api/restaurant/apply/weekend/get/app",
     * tags={"식수 신청 기간"},
     * summary="방학 식수 자동 신청App get",
     * description="방학 식수 자동 신청App get",
     *    
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function getWeekendAutoApp()
    {
        try {
            $weekendAuto = RestaurantWeekendAuto::first();
            $startTime = $weekendAuto->start_time;
            $endTime = $weekendAuto->end_time;
            $startWeek = $weekendAuto->start_week;
            $endWeek = $weekendAuto->end_week;

            $now = Carbon::now();
            $nowWeek = $now->dayOfWeekIso;

            if ($nowWeek >= $startWeek && $nowWeek <= $endWeek) {
                if ($now->between($startTime, $endTime)) {
                    return response()->json(['result' => true]);
                } else {
                    return response()->json(['result' => false]);
                }
            }return response()->json(['result' => false]);
        }catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }
    }



     /**
     * @OA\Post (
     * path="/api/restaurant/apply/semester/auto",
     * tags={"식수 신청 기간"},
     * summary="학기 식수 신청 날짜 디폴트 넣기",
     * description="학기 식수 신청 날짜 디폴트 넣기",
     *    
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function onSemesterAuto()//디폴트로 설정
    {
        $startDate = Carbon::create(null, 3, 1);
        $endDate = Carbon::create(null, 6, 20);

        RestaurantSemesterAuto::create([
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        return response()->json(['message' => '학기 식수 신청 날짜 셋팅 완료되었습니다.']); 
    }


    /**
     * @OA\Patch (
     * path="/api/restaurant/apply/semester/set",
     * tags={"식수 신청 기간"},
     * summary="학기 식수 자동 신청 설정",
     * description="학기 식수 자동 신청 설정",
     *     @OA\RequestBody(
     *         description="설정할 시간",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="start_date", type="string", description="시작 날짜", example="2024-03-19"),
     *                 @OA\Property (property="end_date", type="string", description="종료 날짜", example="2024-06-22"),
     *                 @OA\Property (property="state", type="boolean", description="열림/닫힘", example=true),
     *             )
     *         )
     *     ),
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function setSemesterAuto(Request $request)
        {
            try {
                // 유효성 검사
                $validatedData = $request->validate([
                    'start_date' => 'required|string',
                    'end_date' => 'required|string',
                    'state' =>'required|boolean'
                ]);
            }catch (ValidationException $exception) {
                return response()->json(['error' => $exception->getMessage()], 422);
            }
        
            //어차피 하나밖에 없음
            $apply = RestaurantSemesterAuto::first();
        
            try{
                $apply->update([
                'start_date' => $validatedData['start_date'],
                'end_date' => $validatedData['end_date'],
                'state'=> $validatedData['state'],
                ]);

                $manual = RestaurantApplyManual::where('division', "semester")->first();
                $manual->update([
                'state' => false
            ]);

                $state = RestaurantApplyState::first();
                $state->update([
                    'weekend' => false,
                    'semester' => true
                ]);

                return response()->json(['message' => '학기 식수 신청 시간 수정이 완료되었습니다.']); 
            }catch (ValidationException $exception) {
                return response()->json(['error' => $exception->getMessage()], 422);
            }
        }



        /**
     * @OA\Get (
     * path="/api/restaurant/apply/semester/get",
     * tags={"식수 신청 기간"},
     * summary="학기 식수 자동 신청 get",
     * description="학기 식수 자동 신청 get",
     *    
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
        public function getSemesterAuto()
        {
            try {
                $semesterAuto = RestaurantSemesterAuto::first();
                Log::info('open: ' . $semesterAuto);
                return response()->json(['semesterAuto' => $semesterAuto]);
            }catch (ValidationException $exception) {
                return response()->json(['error' => $exception->getMessage()], 422);
            }
        }


         /**
     * @OA\Get (
     * path="/api/restaurant/apply/semester/get/app",
     * tags={"식수 신청 기간"},
     * summary="학기 식수 자동 신청App get",
     * description="학기 식수 자동 신청App get",
     *    
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function getSemesterAutoApp()
    {
        try {
            $semesterAuto = RestaurantSemesterAuto::first();
            $startDate = $semesterAuto->start_date;
            $endDate = $semesterAuto->end_date;
            $now = Carbon::now();

            if ($now->between($startDate, $endDate)) {
                return response()->json(['result' => true]);
            } else {
                return response()->json(['result' => false]);
            }
        }catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }
    }


    /**
     * @OA\Post (
     * path="/api/restaurant/apply/manual",
     * tags={"식수 신청 기간"},
     * summary="식수 수동 신청 디폴트 넣기",
     * description="식수 수동 디폴트 넣기",
     *     @OA\RequestBody(
     *         description="학기인지 방학인지, 열지 닫을지",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="division", type="string", description="학기,방학 구분", example="semester or weekend"),
     *                 @OA\Property (property="state", type="boolean", description="열림/닫힘", example="true"),
     *             )
     *         )
     *     ),
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function setManual()//디폴트로 들어가야하는게 학기,방학 2개임
    {
        RestaurantApplyManual::create([
            'division' => 'semester',
            'state' => false
        ]);
        RestaurantApplyManual::create([
            'division' => 'weekend',
            'state' => false
        ]);
        return response()->json(['message' => '학기 식수 신청 날짜 셋팅 완료되었습니다.']); 
    }


 /**
     * @OA\Patch(
     * path="/api/restaurant/apply/manual/set",
     * tags={"식수 신청 기간"},
     * summary="식수 수동 신청 수정",
     * description="식수 수동 신청 수정",
     *     @OA\RequestBody(
     *         description="학기인지 방학인지, 열지 닫을지",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="division", type="string", description="학기,방학 구분", example="semester or weekend"),
     *                 @OA\Property (property="state", type="boolean", description="상태", example="true"),
     *             )
     *         )
     *     ),
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function manual(Request $request)
    {
        try {
            
            $validatedData = $request->validate([
                'division' => 'required|string|in:semester,weekend',
                'state' => 'required|boolean'
            ]);
        }catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }

        try{
            $apply = RestaurantApplyManual::where('division', $validatedData['division'])->firstOrNew();
            $apply->state = $validatedData['state'];
            $apply->save();

            if($validatedData['state'] == "semester"){
                RestaurantSemesterAuto::query()->update(['state' => false]);
                $state = RestaurantApplyState::first();
                $state->update([
                    'weekend' => false,
                    'semester' => true
                ]);
            }elseif($validatedData['state'] == "weekend"){
                RestaurantWeekendAuto::query()->update(['state' => false]);
                $state = RestaurantApplyState::first();
                $state->update([
                    'weekend' => true,
                    'semester' => false
                ]);
            }
            
            return response()->json(['message' => $validatedData['division'].'식수 신청 시간 수정이 완료되었습니다.']);
        }catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }
    }



     /**
     * @OA\Get(
     * path="/api/restaurant/apply/manual/get",
     * tags={"식수 신청 기간"},
     * summary="식수 수동 신청 get",
     * description="식수 수동 신청 get",
     *     @OA\RequestBody(
     *         description="학기인지 방학인지",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="division", type="string", description="학기,방학 구분", example="semester or weekend"),
     *             )
     *         )
     *     ),
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function getManual(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'division' => 'required|string|in:semester,weekend',
            ]);
        }catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }

        try {
            $apply = RestaurantApplyManual::where('division', $validatedData['division'])->firstOrFail();
            return response()->json(['open' => $apply->state]);
        }catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }
    }


    /**
     * @OA\Get(
     * path="/api/restaurant/apply/state",
     * tags={"식수 신청 기간"},
     * summary="식수 신청 기간 on/off 확인",
     * description="식수 신청 기간 on/off 확인",
     *     
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function showApplyState()
    {
        $applyState = RestaurantApplyState::all();
        return response()->json(['applyState' => $applyState]);
    }


    /**
     * @OA\Post(
     * path="/api/restaurant/apply/state/on",
     * tags={"식수 신청 기간"},
     * summary="식수 신청 기간 셋팅",
     * description="식수 신청 기간 셋팅",
     *     
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function onApplyState()
    {
        RestaurantApplyState::create([
            'semester' => false,
            'weekend' => false
        ]);
        return response()->json(['message' => '식수 신청 상태 셋팅 완료되었습니다.']);
    }


    /**
     * @OA\Get(
     * path="/api/restaurant/apply/state/check/semester",
     * tags={"식수 신청 기간"},
     * summary="학기 식수 신청 기간 app",
     * description="학기 식수 신청 기간 app에서 확인",
     *     
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function semesterCheck()
    {
        $semester = RestaurantApplyState::pluck('semester')->first();
        $autoState = RestaurantSemesterAuto::pluck('state')->first();
        Log::info('확인semester: '.$semester.'확인state: '.$autoState);
        if($semester == true and $autoState == true){
            Log::info('트루 트루');
            try {
                $semesterAuto = RestaurantSemesterAuto::first();
                $startDate = $semesterAuto->start_date;
                $endDate = $semesterAuto->end_date;
                $now = Carbon::now();
    
                if ($now->between($startDate, $endDate)) {
                    return response()->json(['result' => true]);
                } else {
                    return response()->json(['result' => false]);
                }
            }catch (ValidationException $exception) {
                return response()->json(['error' => $exception->getMessage()], 422);
            }
        } elseif ($semester == true and $autoState == false) {
            Log::info('트루 펄스');
            $autoState = RestaurantApplyManual::where('division', "semester")->first('state');
            return response()->json(['state' => $autoState]);
        }
        return response()->json(['semester' => $semester]);
    }

    
    /**
     * @OA\Get(
     * path="/api/restaurant/apply/state/check/weekend",
     * tags={"식수 신청 기간"},
     * summary="주말 식수 신청 기간 app",
     * description="주말 식수 신청 기간 app에서 확인",
     *     
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function weekendCheck()
    {
        $weekend = RestaurantApplyState::pluck('weekend')->first();
        $autoState = RestaurantWeekendAuto::pluck('state')->first();

        if($weekend == true and $autoState == true){
            try {
                $weekendAuto = RestaurantWeekendAuto::first();
                $startTime = $weekendAuto->start_time;
                $endTime = $weekendAuto->end_time;
                $startWeek = $weekendAuto->start_week;
                $endWeek = $weekendAuto->end_week;
    
                $now = Carbon::now();
                $nowWeek = $now->dayOfWeekIso;
    
                if ($nowWeek >= $startWeek && $nowWeek <= $endWeek) {
                    if ($now->between($startTime, $endTime)) {
                        return response()->json(['result' => true]);
                    } else {
                        return response()->json(['result' => false]);
                    }
                }return response()->json(['result' => false]);
            }catch (ValidationException $exception) {
                return response()->json(['error' => $exception->getMessage()], 422);
            }
        } elseif ($weekend == true and $autoState == false) {
            $autoState = RestaurantApplyManual::first('state');
            return response()->json(['state' => $autoState]);
        }
        return response()->json(['weekend' => $weekend]);
    }
}
