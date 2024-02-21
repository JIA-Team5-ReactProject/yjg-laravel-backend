<?php

namespace App\Http\Controllers;

use App\Models\RestaurantMealType;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RestaurantMealTypeController extends Controller
{
    //
    public function store(Request $request)
    {
        try {
            // 유효성 검사
            $validatedData = $request->validate([
                'meal_type' => 'required|string|size:1',
                'meal_genre' => 'required|string|size:1',
                'content' => 'nullable|string',
                'price' => 'required|integer',
                'weekend' => 'required|boolean',
            ]);
        } catch (ValidationException $exception) {
            // 유효성 검사 실패시 애러 메세지
            return response()->json(['error' => $exception->getMessage()], 422);
        }

        try {
            // 데이터베이스에 저장
            RestaurantMealType::create([
                'meal_type' => $validatedData['meal_type'],
                'meal_genre' =>$validatedData['meal_genre'],
                'content' =>$validatedData['content'],
                'price' =>$validatedData['price'],
                'weekend' =>$validatedData['weekend'],
            ]);
        } catch (\Exception $exception) {//Exception는 부모 예외 클래스임
            // 데이터베이스 저장 실패시 애러 메세지
            return response()->json(['error' => '데이터베이스에 저장하는 중에 오류가 발생했습니다.'], 500);
        }
        
        // 성공 메시지
        return response()->json(['message' => '식사 유형 저장 완료']);
    }
}
