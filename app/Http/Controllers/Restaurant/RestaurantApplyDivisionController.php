<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\RestaurantApplyManual;
use App\Models\RestaurantApplyState;
use App\Models\RestaurantSemesterAuto;
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
        return response()->json(['message' => __('messages.200')]);
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
            ]);

            return response()->json(['message' => __('messages.200')]);
          }catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
          }
    }


//   일단 스웨거 문서에는 없음
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
        return response()->json(['message' => __('messages.200')]);
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
    public function setSemesterAuto(Request $request): \Illuminate\Http\JsonResponse
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
                    'semester' => true,
                ]);

                return response()->json(['message' => __('messages.200')]);
            }catch (ValidationException $exception) {
                return response()->json(['error' => $exception->getMessage()], 422);
            }
        }



//    일단 스웨거 문서에는 없음
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
    public function onManual()//디폴트로 들어가야하는게 학기,방학 2개임
    {
        RestaurantApplyManual::create([
            'division' => 'semester',
            'state' => false
        ]);
        RestaurantApplyManual::create([
            'division' => 'weekend',
            'state' => false
        ]);
        return response()->json(['message' => __('messages.200')]);
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
    public function setManual(Request $request): \Illuminate\Http\JsonResponse
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

            if(!$validatedData['state']){
                if ($validatedData['division'] == "semester") {
                    RestaurantSemesterAuto::query()->update(['state' => false]);
                    $state = RestaurantApplyState::first();
                    $state->update([
                        'semester' => false
                    ]);
                }if ($validatedData['division'] == "weekend") {
                    RestaurantWeekendAuto::query()->update(['state' => false]);
                    $state = RestaurantApplyState::first();
                    $state->update([
                        'weekend' => false
                    ]);
                }
            }


            if($validatedData['division'] == "semester" and $validatedData['state']){
                RestaurantSemesterAuto::query()->update(['state' => false]);
                $state = RestaurantApplyState::first();
                $state->update([
                    'semester' => true
                ]);
            }if($validatedData['division'] == "weekend" and $validatedData['state']){
                RestaurantWeekendAuto::query()->update(['state' => false]);
                $state = RestaurantApplyState::first();
                $state->update([
                    'weekend' => true,
                ]);
            }

            return response()->json(['message' => $validatedData['division'].__('messages.200')]);
        }catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }
    }


    //일단 스웨거 문서에는 없음
    public function showApplyState(): \Illuminate\Http\JsonResponse
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
    public function onApplyState(): \Illuminate\Http\JsonResponse
    {
        RestaurantApplyState::create([
            'semester' => false,
            'weekend' => false
        ]);
        return response()->json(['message' => __('messages.200')]);
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
    public function semesterCheck(): \Illuminate\Http\JsonResponse
    {
        $semester = RestaurantApplyState::pluck('semester')->first();
        $autoState = RestaurantSemesterAuto::pluck('state')->first();
        $semesterManual = RestaurantApplyManual::where('division', "semester")->pluck('state')->first();
        if($semester and $autoState){
            try {
                $semesterAuto = RestaurantSemesterAuto::first();
                $startDate = Carbon::createFromFormat('m-d', $semesterAuto->start_date);
                $endDate = Carbon::createFromFormat('m-d', $semesterAuto->end_date);
                $now = Carbon::now();
                if ($now->between($startDate, $endDate)){
                    return response()->json(['auto' => 1]);
                } else {
                    return response()->json(['auto' => 0]);
                }
            }catch (ValidationException $exception) {
                return response()->json(['error' => $exception->getMessage()], 422);
            }
        } if ($semester and $semesterManual) {
            return response()->json(['manual' => $semesterManual]);
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
    public function weekendCheck(): \Illuminate\Http\JsonResponse
    {
        $weekend = RestaurantApplyState::pluck('weekend')->first();
        $autoState = RestaurantWeekendAuto::pluck('state')->first();
        $weekendManual = RestaurantApplyManual::where('division', "weekend")->pluck('state')->first();

        if($weekend and $autoState){
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
                        return response()->json(['auto' => 1]);
                    } else {
                        return response()->json(['auto' => 0]);
                    }
                }
            }catch (ValidationException $exception) {
                return response()->json(['error' => $exception->getMessage()], 422);
            }
        }if($weekend and $weekendManual) {
            return response()->json(['manual' => $weekendManual]);
        }
        return response()->json(['weekend' => $weekend]);
    }


     /**
     * @OA\Get(
     * path="/api/restaurant/apply/state/web/semester",
     * tags={"식수 신청 기간"},
     * summary="학기 식수 신청 기간 web 페이지 확인",
     * description="학기 식수 신청 기간 web 페이지 확인",
     *
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function webSemester(): \Illuminate\Http\JsonResponse
    {
        try{
            $manualSemester = RestaurantApplyManual::where('division', "semester")->pluck('state')->first();
            $semester = RestaurantApplyManual::where('division', "semester")->first();
            $autoSemester = RestaurantSemesterAuto::first();
            if ($manualSemester) {
                return response()->json(['manual' => $semester]);
            }if ($manualSemester == false and $autoSemester->state) {
                return response()->json(['auto' => $autoSemester]);
            }else{
                return response()->json(['manual' => $semester]);
            }
        }catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }
    }


     /**
     * @OA\Get(
     * path="/api/restaurant/apply/state/web/weekend",
     * tags={"식수 신청 기간"},
     * summary="주말 식수 신청 기간 web 페이지 확인",
     * description="주말 식수 신청 기간 web 페이지 확인",
     *
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function webWeekend(): \Illuminate\Http\JsonResponse
    {
        try{
            $weekend = RestaurantApplyManual::where('division', "weekend")->first();
            $manualWeekend = RestaurantApplyManual::where('division', "weekend")->pluck('state')->first();
            $autoWeekend = RestaurantWeekendAuto::first();
            if ($manualWeekend) {
                return response()->json(['manual' => $weekend]);
            } if (!$manualWeekend and $autoWeekend->state) {
                return response()->json(['weekendAutoData' => $autoWeekend]);
            } else {
                return response()->json(['manual' => $weekend]);
            }
        }catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }
    }

}
