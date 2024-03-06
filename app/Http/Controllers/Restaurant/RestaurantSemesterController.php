<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\RestaurantSemester;
use App\Models\RestaurantSemesterMealType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

// 예외 처리

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
                'id' => 'required|string',
            ]);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }
        try {

            // 데이터베이스에 저장
            $restaurantSemester = RestaurantSemester::create([
                'user_id' => $validatedData['user_id'],
                //'payment' => $validatedData['payment'],
            ]);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }

        try {
            // $semesterMealTypeId = SemesterMealType::where("id", $validatedData["id"])
            // ->first();
            RestaurantSemesterMealType::create([
            'restaurant_semester_id' => $restaurantSemester->id,
            'semester_meal_type_id' => $validatedData["id"]
            ]);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
        return response()->json(['message' => '식수 학기 신청이 완료되었습니다.']);
    }

    /**
         * @OA\Get (
         * path="/api/restaurant/semester/payment",
         * tags={"식수"},
         * summary="식수 학기 신청 입금여부",
         * description="식수 학기 신청의 입금여부를 확인 합니다",
         *     @OA\RequestBody(
         *         description="학생 식사 신청 입금여부",
         *         required=true,
         *         @OA\MediaType(
         *             mediaType="application/json",
         *             @OA\Schema (
         *                 @OA\Property (property="user_id", type="string", description="사용자 ID", example="1"),
         *             )
         *         )
         *     ),
         *  @OA\Response(response="200", description="Success"),
         *  @OA\Response(response="500", description="Fail"),
         * )
         */
    public function getPayment(Request $request)
    {

        try {
            // 유효성 검사
            $validatedData = $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }

        try {
            $paymentData = RestaurantSemester::where('user_id', $validatedData['user_id'])->pluck('payment');
            return response()->json(['payment_data' => $paymentData]);
        } catch (\Exception $exception) {
            return response()->json(['error' => '페이먼트 데이터 조회 중 오류가 발생했습니다.'], 500);
        }
    }

    public function setPayment(Request $request)
    {
        try {
            // 유효성 검사
            $validatedData = $request->validate([
                'user_id' => 'required|exists:users,id',
                'payment' => 'required|boolean',
            ]);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }


        try {
            Log::info('유저 아이디: ' . $validatedData['user_id']);
            Log::info('페이먼트: ' . $validatedData['payment']);
                $user = RestaurantSemester::findOrFail($validatedData['user_id']);
                $user->payment = $validatedData['payment'];
                $user->save();
            } catch (\Exception $exception) {
                return response()->json(['error' => $exception->getMessage()], 500);
            }
            return response()->json(['message' => '입금이 확인 되었습니다.']);
        }
    }
