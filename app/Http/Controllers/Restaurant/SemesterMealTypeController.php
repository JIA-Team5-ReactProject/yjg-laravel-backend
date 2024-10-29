<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\SemesterMealType;
use App\Models\WeekendMealType;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SemesterMealTypeController extends Controller
{
     /**
     * @OA\Post (
     * path="/api/semester/meal-type",
     * tags={"식수 유형"},
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
    public function store(Request $request): \Illuminate\Http\JsonResponse
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
        return response()->json(['message' => __('messages.200')]);
    }


    /**
     * @OA\Delete (
     *     path="/api/restaurant/semester/m/delete/{id}",
     *     tags={"식수 유형"},
     *     summary="학기 식수 유형 삭제",
     *     description="학기 식수 유형 삭제",
     *     @OA\Parameter(
     *           name="id",
     *           description="삭제할 학기 식수 유형 아이디",
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
            $SemesterMealType = SemesterMealType::findOrFail($id);

            $SemesterMealType->delete();

            return response()->json(['message' => __('messages.200')]);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }



     /**
     * @OA\Get (
     *     path="/api/restaurant/semester/meal-type/get",
     *     tags={"식수 유형"},
     *     summary="학기 식수 유형 가져오기",
     *     description="학기 식수 유형 가져오기",
     *
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function getMealType(): \Illuminate\Http\JsonResponse
    {
        try{
            $mealType = SemesterMealType::all();
            return response()->json(['semester_meal_type' => $mealType]);
        }catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }



    /**
     * @OA\Patch (
     * path="/api/restaurant/semester/m/update/{id}",
     * tags={"식수 유형"},
     * summary="학기 식수 유형 수정",
     * description="학기 식수 유형을 수정",
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
     *     @OA\Parameter(
     *           name="id",
     *           description="수정할 학기 식수 유형 아이디",
     *           required=true,
     *           in="path",
     *           @OA\Schema(type="integer"),
     *     ),
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function update(Request $request, $id): \Illuminate\Http\JsonResponse
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
            $SemesterMealType = SemesterMealType::findOrFail($id);

            $SemesterMealType->update([
                'meal_type' => $validatedData['meal_type'],
                'content' =>$validatedData['content'],
                'price' =>$validatedData['price'],
            ]);

            return response()->json(['message' => __('messages.200')]);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }
}
