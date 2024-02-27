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
                'meal_type' => 'required',
            ]);
        } catch (ValidationException $exception) {
            // 유효성 검사 실패시 애러 메세지
            return response()->json(['error' => $exception->getMessage()], 422);
        }



        try {
            // 데이터베이스에 저장
            $restaurantSemester = RestaurantSemester::create([
                'user_id' => $validatedData['user_id'],
            ]);
        } catch (\Exception $exception) {//Exception는 부모 예외 클래스임
            // 데이터베이스 저장 실패시 애러 메세지
            return response()->json(['error' => $exception->getMessage()], 500);
        }

        try {
            SemesterMealType::create([
                'id' => $validatedData['meal_type'],

            ]);

        } catch (\Exception $exception) {//Exception는 부모 예외 클래스임
            // 데이터베이스 저장 실패시 애러 메세지
            return response()->json(['error' => $exception->getMessage()], 500);
        }
        // 성공 메시지
        return response()->json(['message' => '식수 학기 신청이 완료되었습니다.']);
    }

    public function getPayment(Request $request)
    {
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
        try {
            // 유효성 검사
            $validatedData = $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }

        try {
            // 데이터베이스에서 해당 사용자의 페이먼트 데이터 조회
            $paymentData = RestaurantSemester::where('user_id', $validatedData['user_id'])->pluck('payment');
            // 조회된 데이터 반환
            return response()->json(['payment_data' => $paymentData]);
        } catch (\Exception $exception) {
            // 데이터 조회 실패시 에러 메시지 반환
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
                // 기존 사용자의 결제 정보를 업데이트
                $user = RestaurantSemester::findOrFail($validatedData['user_id']);
                $user->payment = $validatedData['payment'];
                $user->save();
            } catch (\Exception $exception) {//Exception는 부모 예외 클래스임
                return response()->json(['error' => '데이터베이스에 저장하는 중에 오류가 발생했습니다.'], 500);
            }
            return response()->json(['message' => '입금이 확인 되었습니다.']);
        }
    }
