<?php

namespace App\Http\Controllers;

use App\Models\BusRoute;
use App\Models\BusTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class busTimeController extends Controller
/**
     * @OA\Post (
     * path="/api/bus/time",
     * tags={"버스"},
     * summary="버스 시간표 등록",
     * description="버스 시간표 및 노선을 등록합니다",
     *     @OA\RequestBody(
     *         description="버스 노선 시간, 정류장 정보",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="weekend", type="boolean", description="평일/주말", example=true),
     *                 @OA\Property (property="bus_route_direction", type="string", description="노선", example="B"),
     *                 @OA\Property (property="bokhyun", type="time", description="영진 복현 서문", example="08:00"),
     *                 @OA\Property (property="woobang", type="time", description="우방", example="08:00"),
     *                 @OA\Property (property="city", type="time", description="시티병원", example="08:00"),
     *                 @OA\Property (property="sk", type="time", description="sk빌딩", example="08:00"),
     *                 @OA\Property (property="dc", type="time", description="동천119안전센터", example="08:00"),
     *                 @OA\Property (property="bukgu", type="time", description="북구문화예술회관", example="08:00"),
     *                 @OA\Property (property="bank", type="time", description="기업은행칠곡점", example="08:00"),
     *                 @OA\Property (property="taejeon", type="time", description="태전역", example="08:00"),
     *                 @OA\Property (property="g_domitory", type="time", description="글로벌생활관", example="08:00"),
     *                 @OA\Property (property="g_campus", type="time", description="글로벌캠퍼스", example="08:00"),
     *                 @OA\Property (property="en", type="time", description="영어마을", example="08:00"),
     *                 @OA\Property (property="munyang", type="time", description="문양역", example="08:00"),
     *             )
     *         )
     *     ),
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
{
    public function store(Request $request)
    {
        try {
            // 유효성 검사
            $validatedData = $request->validate([
                'weekend' => 'required|boolean',
                'bus_route_direction' => 'required|string|size:1',
                'bokhyun' => 'required|date_format:H:i',
                'woobang' => 'required|date_format:H:i',
                'city' => 'required|date_format:H:i',
                'sk' => 'required|date_format:H:i',
                'dc' => 'required|date_format:H:i',
                'bukgu' => 'required|date_format:H:i',
                'bank' => 'required|date_format:H:i',
                'taejeon' => 'required|date_format:H:i',
                'g_campus' => 'required|date_format:H:i',
                'en' => 'required|date_format:H:i',
                'munyang' => 'required|date_format:H:i',
            ]);
        } catch (ValidationException $exception) {
            // 유효성 검사 실패시 애러 메세지
            return response()->json(['error' => $exception->getMessage()], 422);
        }

        try{
            $busRoute = BusRoute::where('weekend', $validatedData['weekend'])
                             ->where('bus_route_direction', $validatedData['bus_route_direction'])
                             ->firstOrFail(); // 일치하는 항목이 없으면 예외 발생
        } catch (\Exception $exception) {
            return response()->json(['error' => '해당하는 버스 노선을 찾을 수 없습니다.'], 404);
        }   

        Log::info('버스노선 아이디: ' . $busRoute->id);
        try {
            // 데이터베이스에 저장
            BusTime::create([
                'bus_route_id' => $busRoute->id,
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
            return response()->json(['error' => $exception->getMessage()], 500);
        }
        
        // 성공 메시지
        return response()->json(['message' => '버스 시간표 저장 완료']);
    }
}
