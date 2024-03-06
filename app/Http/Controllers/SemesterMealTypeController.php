<?php

namespace App\Http\Controllers;

use App\Models\SemesterMealType;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SemesterMealTypeController extends Controller
{
    /**
     * @OA\Post (
     * path="/api/semester/mealtype",
     * tags={"식수"},
     * summary="학기 식수 유형 생성",
     * description="학기 식수 유형을 생성",
     *     @OA\RequestBody(
     *         description="만들 학기 식수 유형의 데이터",
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
            $validatedData = $request->validate([
                'meal_type' => 'required|string',
                'content' => 'nullable|string',
                'price' => 'required|string',
            ]);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }

        try {
            SemesterMealType::create([
                'meal_type' => $validatedData['meal_type'],
                'content' =>$validatedData['content'],
                'price' =>$validatedData['price'],
            ]);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
        return response()->json(['message' => '학기 식사 유형 저장 완료']);
    }
}
