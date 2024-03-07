<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\RestaurantWeekend;
use App\Models\RestaurantWeekendMealType;
use App\Models\WeekendMealType;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RestaurantWeekendController extends Controller
{
    /**
     * @OA\Post (
     *     path="/api/restaurant/weekend",
     *     tags={"식수"},
     *     summary="식수 주말 신청",
     *     description="식수 주말 신청을 처리합니다",
     *         @OA\RequestBody(
     *             description="학생 식사 신청 정보",
     *             required=true,
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema (
     *                     @OA\Property (property="user_id", type="string", description="사용자 ID", example="1"),
     *                     @OA\Property (property="meal_type", type="string", description="식사유형", example="A"),
     *                 )
     *             )
     *         ),
     *         @OA\Response(response="200", description="Success"),
     *         @OA\Response(response="500", description="Fail"),
     * )
     */
    public function store(Request $request)
    {
        try {
            // 유효성 검사
            $validatedData = $request->validate([
                'meal_type' => 'required|string',
                'user_id' => 'required|exists:users,id',
            ]);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }

        try {
            // 데이터베이스에 저장
            $RestaurantWeekend = RestaurantWeekend::create([
                'user_id' => $validatedData['user_id'],
            ]);
        } catch (\Exception $exception) {
            return response()->json(['error' =>  $exception->getMessage()], 500);
        }

        $weekendMealType = WeekendMealType::where('meal_type', $validatedData['meal_type'])
                                            ->first();
        RestaurantWeekendMealType::create([
            'restaurant_weekend_id' => $RestaurantWeekend->id,
            'weekend_meal_type_id' => $weekendMealType->id
        ]);

        // 완료 메시지 주기
        return response()->json(['message' => '주말 식수 신청이 완료되었습니다.']);
    }
    
       /**
     * @OA\get (
     *     path="/api/restaurant/weekend/g/payment/{id}",
     *     tags={"식수"},
     *     summary="주말 식수 입금여부 확인",
     *     description="주말 식수 입금여부 확인",
     *     @OA\Parameter(
     *           name="id",
     *           description="확인할 식수신청 id",
     *           required=true,
     *           in="path",
     *           @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function getPayment($id)
    {
        try {
            $paymentData = RestaurantWeekend::where('id', $id)->pluck('payment');
            return response()->json(['payment_data' => $paymentData]);
        } catch (\Exception $exception) {
            return response()->json(['error' => '페이먼트 데이터 조회 중 오류가 발생했습니다.'], 500);
        }
    }
    
/**
     * @OA\Post (
     *     path="/api/restaurant/weekend/p/payment/{id}",
     *     tags={"식수"},
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
    public function setPayment(Request $request, $id)
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
            return response()->json(['message' => '입금이 확인 되었습니다.']);
    }

    /**
     * @OA\Delete (
     *     path="/api/restaurant/weekend/delete/{id}",
     *     tags={"식수"},
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
    public function delete($id)
    {
        try {
            $RestaurantWeekend = RestaurantWeekend::findOrFail($id);

            $RestaurantWeekend->delete();

            return response()->json(['message' => '주말 식수 신청이 삭제되었습니다.']);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }
}
