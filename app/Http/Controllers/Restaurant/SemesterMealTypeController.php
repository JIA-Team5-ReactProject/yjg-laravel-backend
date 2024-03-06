<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\SemesterMealType;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SemesterMealTypeController extends Controller
{
     /**
     * @OA\Post (
     * path="/api/restaurant/semester/meal-type",
     * tags={"식수"},
     * summary="학기 식수 유형 생성",
     * description="학기 식수 유형을 생성",
     *     @OA\RequestBody(
     *         description="학기 식수 유형 정보",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="meal_type", type="string", description="식사 유형", example="C"),
     *                 @OA\Property (property="content", type="string", description="설명", example="점심+저녁"),
     *                 @OA\Property (property="price", type="string", description="가격", example="5600"),
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
                'meal_type' => 'required|string',
                'content' => 'nullable|string',
                'price' => 'required|string',
            ]);
        } catch (ValidationException $exception) {
            // 유효성 검사 실패시 애러 메세지
            return response()->json(['error' => $exception->getMessage()], 422);
        }

        try {
            // 데이터베이스에 저장
            SemesterMealType::create([
                'meal_type' => $validatedData['meal_type'],
                'content' =>$validatedData['content'],
                'price' =>$validatedData['price'],
            ]);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }

        // 성공 메시지
        return response()->json(['message' => '학기 식사 유형 저장 완료']);
    }
}
