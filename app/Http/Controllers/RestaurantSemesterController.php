<?php

namespace App\Http\Controllers;

use App\Models\RestaurantSemesterMealType;
use App\Models\SemesterMealType;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException; // 예외 처리
use App\Models\RestaurantSemester;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class RestaurantSemesterController extends Controller
{
    /**
     * @OA\Post (
     * path="/api/restaurant/semester",
     * tags={"식수"},
     * summary="식수 학기 신청",
     * description="식수 학기 신청을 처리합니다",
     *     @OA\RequestBody(
     *         description="학생 식사 신청 정보",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="user_id", type="string", description="사용자 ID", example="1"),
     *                 @OA\Property (property="payment", type="boolean", description="입금 확인", example=false),
 *                     @OA\Property (property="meal_type", type="string", description="식사 유형", example="C"),
     *             )
     *         )
     *     ),
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function store(Request $request)
    {
        try {
            // 유효성 검사
            $validatedData = $request->validate([
                'user_id' => 'required|exists:users,id',
                'payment' => 'required|boolean',
                'meal_type' => 'required|string',
            ]);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }
        try {
            
            // 데이터베이스에 저장
            $restaurantSemester = RestaurantSemester::create([
                'user_id' => $validatedData['user_id'],
                
            ]);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }

        try {
            $semesterMealTypeId = SemesterMealType::where("meal_type", $validatedData["meal_type"])
            ->first();
            RestaurantSemesterMealType::create([
            'restaurant_semester_id' => $restaurantSemester->id,
            'semester_meal_type_id' => $semesterMealTypeId->id
            ]);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
        return response()->json(['message' => '식수 학기 신청이 완료되었습니다.']);
    }

    /**
         * @OA\Get (
         * path="/api/restaurant/semester/g/payment/{id}",
         * tags={"식수"},
         * summary="식수 학기 신청 입금여부",
         * description="식수 학기 신청의 입금여부를 확인 합니다",
         *    @OA\Parameter(
     *           name="id",
     *           description="조회 할 식수 신청 아이디",
     *           required=true,
     *           in="path",
     *           @OA\Schema(type="integer"),
     *     ),
         *  @OA\Response(response="200", description="Success"),
         *  @OA\Response(response="500", description="Fail"),
         * )
         */
    public function getPayment($id)
    {
        try {
            $paymentData = RestaurantSemester::where('id', $id)->pluck('payment');
            return response()->json(['payment_data' => $paymentData]);
        } catch (\Exception $exception) {
            return response()->json(['error' => '페이먼트 데이터 조회 중 오류가 발생했습니다.'], 500);
        }
    }

    /**
     * @OA\Patch (
     *     path="/api/restaurant/semester/p/payment/{id}",
     *     tags={"식수"},
     *     summary="학기 입금여부 수정",
     *     description="학기 입금여부를 수정",
     *      @OA\Parameter(
     *           name="id",
     *           description="확인할 식수신청 id",
     *           required=true,
     *           in="path",
     *           @OA\Schema(type="integer"),
     *          ),
     *     @OA\RequestBody(
     *         description="수정할 입금여부(true,false), 신청id",
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
                $apply_id = RestaurantSemester::findOrFail($id);
                $apply_id->payment = $validatedData['payment'];
                $apply_id->save();
            } catch (\Exception $exception) {
                return response()->json(['error' => $exception->getMessage()], 500);
            }
            return response()->json(['message' => '입금이 확인 되었습니다.']);
        }
    }
