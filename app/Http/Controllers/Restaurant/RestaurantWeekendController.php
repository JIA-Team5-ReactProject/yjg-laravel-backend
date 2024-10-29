<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Resources\WeekendApplyResource;
use App\Models\RestaurantWeekend;
use App\Models\RestaurantWeekendMealType;
use App\Models\WeekendMealType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RestaurantWeekendController extends Controller
{
    /**
     * @OA\Post (
     *     path="/api/restaurant/weekend",
     *     tags={"식수 신청 주말"},
     *     summary="식수 주말 신청",
     *     description="식수 주말 신청을 처리합니다",
     *         @OA\RequestBody(
     *             description="학생 식사 신청 정보",
     *             required=true,
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema (
     *                     @OA\Property (property="meal_type", type="string", description="식사유형", example="A"),
     *                     @OA\Property (property="refund", type="boolean", description="환불여부", example=true),
     *                     @OA\Property (property="sat", type="boolean", description="토욜", example=true),
     *                     @OA\Property (property="sun", type="boolean", description="일욜", example=true),
     *                 )
     *             )
     *         ),
     *         @OA\Response(response="200", description="Success"),
     *         @OA\Response(response="500", description="Fail"),
     * )
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // 유효성 검사
            $validatedData = $request->validate([
                'meal_type' => 'required|string',
                'refund' => 'required|boolean',
                'sat' => 'required|boolean',
                'sun' => 'required|boolean',
            ]);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }



        try {
            $user_id = auth('users')->id();
            $mealTypeId = WeekendMealType::where("meal_type", $validatedData["meal_type"])
                                        ->first();
            RestaurantWeekend::create([
                'user_id' => $user_id,
                'weekend_meal_type_id' => $mealTypeId->id,
                'refund' => $validatedData['refund'],
                'sat' => $validatedData['sat'],
                'sun' => $validatedData['sun'],
            ]);
            return response()->json(['message' => __('messages.200')]);
        } catch (\Exception $exception) {
            return response()->json(['error' =>  $exception->getMessage()], 500);
        }
    }

       /**
     * @OA\get (
     *     path="/api/restaurant/weekend/g/payment/{id}",
     *     tags={"식수 신청 주말"},
     *     summary="주말 식수 입금 확인",
     *     description="주말 식수 입금 확인",
     *     @OA\Parameter(
     *           name="id",
     *           description="조회 할 식수 신청 아이디",
     *           required=true,
     *           in="path",
     *           @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function getPayment($id): \Illuminate\Http\JsonResponse
    {
        try {
            $paymentData = RestaurantWeekend::where('id', $id)->pluck('payment');
            return response()->json(['payment_data' => $paymentData]);
        } catch (\Exception $exception) {
            return response()->json(['error' => __('messages.500')], 500);
        }
    }

/**
     * @OA\Post (
     *     path="/api/restaurant/weekend/p/payment/{id}",
     *     tags={"식수 신청 주말"},
     *     summary="주말 입금여부 수정",
     *     description="주말 입금여부를 수정",
     *      @OA\Parameter(
     *           name="id",
     *           description="확인할 식수신청 id",
     *           required=true,
     *           in="path",
     *           @OA\Schema(type="integer"),
     *          ),
     *      @OA\RequestBody(
     *         description="수정할 입금여부(true,false)",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="payment", type="boolean", description="입금여부", example="true"),
     *              )
     *         )
     *     ),
     *     @OA\Response(response="200", description="OK"),
     *     @OA\Response(response="422", description="Validation Exception"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function setPayment(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            // 유효성 검사
            $validatedData = $request->validate([
                'payment' => 'required|boolean',
            ]);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }

        try {
            $apply_id = RestaurantWeekend::findOrFail($id);
            $apply_id->payment = $validatedData['payment'];
            $apply_id->save();
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
        return response()->json(['message' => __('messages.200')]);
    }

    /**
     * @OA\Delete (
     *     path="/api/restaurant/weekend/delete/{id}",
     *     tags={"식수 신청 주말"},
     *     summary="주말 식수 신청 삭제",
     *     description="주말 식수 신청 삭제",
     *     @OA\Parameter(
     *           name="id",
     *           description="삭제할 주말 식수 신청 아이디",
     *           required=true,
     *           in="path",
     *           @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function delete($id): \Illuminate\Http\JsonResponse
    {
        try {
            $RestaurantWeekend = RestaurantWeekend::findOrFail($id);
            $RestaurantWeekend->delete();

            return response()->json(['message' => __('messages.200')]);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }


     /**
     * @OA\Get (
     * path="/api/restaurant/weekend/show",
     * tags={"식수 신청 주말"},
     * summary="주말 식수 신청 리스트 보기, 검색",
     * description="주말 식수 신청 리스트 보기, 검색하기",
     *     @OA\RequestBody(
     *         description="찾고싶은 학생 이름",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="name", type="string", description="학생이름", example="권지훈"),
     *             )
     *         )
     *     ),
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function show(Request $request): \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Http\JsonResponse
    {
        try {
            $name = $request->input('name');
            $allData = RestaurantWeekend::with('weekendMealType:id,meal_type', 'user:id,phone_number,name,student_id');

            if ($name === null || $name === '') {
                $applyData = $allData->paginate(5);
            } else {
                $applyData = $allData->whereHas('user', function ($allData) use ($name) {
                    $allData->where('name', 'like', '%' . $name . '%');
                })->paginate(5);
            }

            return $applyData;
        } catch (\Exception $exception) {
            return response()->json(['applyData' => []]);
        }
    }

/**
     * @OA\Get (
     * path="/api/restaurant/weekend/show/sumApp",
     * tags={"식수 신청 주말"},
     * summary="주말 식수 신청 인원 확인",
     * description="주말 식수 신청 인원 확인",
     *     @OA\RequestBody(
     *         description="찾고싶은 요일",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="date", type="string", description="요일", example="sat"),
     *             )
     *         )
     *     ),
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function sumApplyApp(Request $request): \Illuminate\Http\JsonResponse
    {
        try{
            $validatedData = $request->validate([
                'date' => 'required|string',
            ]);
        }catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }

        try{
            if ($validatedData['date'] == "sun") {
                $sun = RestaurantWeekend::where('sun',1)->get();
                $sunCount = $sun->count();
                return response()->json(['sunCount' => $sunCount]);
            }else{
                $sat = RestaurantWeekend::where('sat', 1)->get();
                $satCount = $sat->count();
                return response()->json(['satCount' => $satCount]);
            }
        }catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }


    /**
     * @OA\Get (
     * path="/api/restaurant/weekend/show/sumWeb",
     * tags={"식수 신청 주말"},
     * summary="주말 식수 신청 인원 확인web",
     * description="주말 식수 신청 인원 확인web",
     *     @OA\RequestBody(
     *         description="찾고싶은 요일",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="date", type="string", description="요일", example="sat"),
     *             )
     *         )
     *     ),
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function sumApplyWeb(Request $request): \Illuminate\Http\JsonResponse
    {
        $mealTypes = weekendMealType::all(['id','meal_type']);
        $applyData = []; // 결과를 저장할 배열 초기화

        if($request->date == 'sat') {
            foreach ($mealTypes as $mealType) {
                // 각 식수 유형별로 해당하는 레코드 수를 조회
                $count = RestaurantWeekend::where('sat', 1)
                                        ->where('weekend_meal_type_id', $mealType->id)
                                        ->count();
                // 조회한 갯수를 식수 유형 ID를 키로 하여 배열에 저장
                $applyData[$mealType->meal_type] = $count;
            }

        } else if($request->date == 'sun') {
            foreach ($mealTypes as $mealType) {
                // 각 식수 유형별로 해당하는 레코드 수를 조회
                $count = RestaurantWeekend::where('sun', 1)
                                        ->where('weekend_meal_type_id', $mealType->id)
                                        ->count();
                // 조회한 갯수를 식수 유형 ID를 키로 하여 배열에 저장
                $applyData[$mealType->meal_type] = $count;
            }
        } else {
            return response()->json(['error' => __('messages.500')], 500);
        }
        return response()->json(['applyData' => $applyData]);
    }



    /**
     * @OA\Get (
     * path="/api/restaurant/weekend/show/user/table",
     * tags={"식수 신청 주말"},
     * summary="주말 식수 신청자 유저 정보",
     * description="주말 식수 유저 정보 확인",
     *
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function showUserTable(): \Illuminate\Http\JsonResponse
    {
        try{
            $user_id = auth('users')->id();
            $allData = RestaurantWeekend::with('weekendMealType:id,meal_type', 'user:id,phone_number,name,student_id');
            $applyData = $allData->where('user_id', $user_id)->paginate(5);

            return response()->json(['userData' => $applyData ]);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()]);
        }
    }
}
