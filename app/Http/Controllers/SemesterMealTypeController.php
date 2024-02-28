<?php

namespace App\Http\Controllers;

use App\Models\SemesterMealType;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SemesterMealTypeController extends Controller
{
    public function store(Request $request)
    {
        try {
            // 유효성 검사
            $validatedData = $request->validate([
                'id' => 'required|string',
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
                'id' => $validatedData['id'],
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
