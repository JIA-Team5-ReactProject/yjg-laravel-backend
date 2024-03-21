<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\WeekendMealType;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WeekendMealTypeController extends Controller
{
    /**
     * @OA\Post (
     *     path="/api/restaurant/weekend/meal-type",
     *     tags={"식수 유형"},
     *     summary="주말 식수 유형 신청",
     *     description="주말 식수 유형 신청을 처리합니다",
     *         @OA\RequestBody(
     *             description="주말 식수 유형 정보",
     *             required=true,
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema (
     *                     @OA\Property (property="meal_type", type="string", description="식사유형", example="A"),
     *                     @OA\Property (property="content", type="string", description="내용", example="아침"), 
     *                     @OA\Property (property="price", type="string", description="가격", example="750,000"),
     *                     
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
                'content' => 'required|string',
                'price' => 'required|string',
                
            ]);
        } catch (ValidationException $exception) {
            // 유효성 검사 실패시 애러 메세지
            return response()->json(['error' => $exception->getMessage()], 422);
        }

        try {
            // 데이터베이스에 저장
            WeekendMealType::create([
                'meal_type' => $validatedData['meal_type'],
                'content' =>$validatedData['content'],
                'price' =>$validatedData['price'],
            ]);
        } catch (\Exception $exception) {//Exception는 부모 예외 클래스임
            // 데이터베이스 저장 실패시 애러 메세지
            return response()->json(['error' => '데이터베이스에 저장하는 중에 오류가 발생했습니다.'], 500);
        }

        // 성공 메시지
        return response()->json(['message' => '주말 식사 유형 저장 완료']);
    }


    /**
     * @OA\Delete (
     *     path="/api/restaurant/weekend/m/delete/{id}",
     *     tags={"식수 유형"},
     *     summary="주말 식수 유형 삭제",
     *     description="주말 식수 유형 삭제",
     *     @OA\Parameter(
     *           name="id",
     *           description="삭제할 주말 식수 유형 아이디",
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
            $RestaurantWeekend = WeekendMealType::findOrFail($id);
            $RestaurantWeekend->delete();

            return response()->json(['message' => '주말 식수 유형이 삭제되었습니다.']);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }


     /**
     * @OA\Get (
     *     path="/api/restaurant/weekend/meal-type/get",
     *     tags={"식수 유형"},
     *     summary="주말 식수 유형 가져오기",
     *     description="주말 식수 유형 가져오기",
     *     
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function getMealType()
    {
        try{
            $mealType = weekendMealType::all();
            return response()->json(['semester_meal_type' => $mealType]);
        }catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }
}
