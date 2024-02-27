<?php

namespace App\Http\Controllers;

use App\Models\BusTime;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class busTimeController extends Controller
{
    public function store(Request $request)
    {
        try {
            // 유효성 검사
            $validatedData = $request->validate([
                'weekend' => 'required|',
                'bus_route_direction' => 'required|time',
                'bokhyun' => 'required|time',
                'woobang' => 'required|time',
                'city' => 'required|time',
                'sk' => 'required|time',
                'dc' => 'required|time',
                'bukgu' => 'required|time',
                'bank' => 'required|time',
                'taejeon' => 'required|time',
                'g_campus' => 'required|time',
                'en' => 'required|time',
                'munyang' => 'required|time',
            ]);
        } catch (ValidationException $exception) {
            // 유효성 검사 실패시 애러 메세지
            return response()->json(['error' => $exception->getMessage()], 422);
        }

        try {
            // 데이터베이스에 저장
            BusTime::create([
                'bokhyun' => $validatedData['bokhyun'],
                'woobang' =>$validatedData['woobang'],
                'city' =>$validatedData['city'],
                'sk' =>$validatedData['sk'],
                'dc' =>$validatedData['dc'],
                'bukgu' =>$validatedData['bukgu'],
                'bank' =>$validatedData['bank'],
                'taejeon' =>$validatedData['taejeon'],
                'g_campus' =>$validatedData['g_campus'],
                'en' =>$validatedData['en'],
                'munyang' =>$validatedData['munyang'],
            ]);
        } catch (\Exception $exception) {//Exception는 부모 예외 클래스임
            // 데이터베이스 저장 실패시 애러 메세지
            return response()->json(['error' => '데이터베이스에 저장하는 중에 오류가 발생했습니다.'], 500);
        }
        
        // 성공 메시지
        return response()->json(['message' => '버스 시간표 저장 완료']);
    }
}
