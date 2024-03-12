<?php

namespace App\Http\Controllers\Restaurant;

use App\Exports\RestaurantMenuExport;
use App\Http\Controllers\Controller;
use App\Imports\RestaurantMenuImport;
use App\Models\RestaurantMenu;
use App\Models\RestaurantMenuDate;
use App\Models\RestaurantMenuMonth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class RestaurantMenusController extends Controller
{
/**
     * @OA\Post (
     * path="/api/restaurant/menu/date",
     * tags={"식수"},
     * summary="식수 식단표 날짜 추가",
     * description="식수 식단표 날짜 추가를 합니다",
     *     @OA\RequestBody(
     *         description="추가할 날짜(년,월)",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="month", type="string", description="월", example="03"),
 *                     @OA\Property (property="year", type="string", description="년", example="23"),
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
                'month' => 'required|string',
                'year' => 'required|string'
            ]);
        } catch (ValidationException $exception) {
            // 유효성 검사 실패시 애러 메세지
            return response()->json(['error' => $exception->getMessage()], 422);
        }
       
        try {
            // 데이터베이스에 저장
            RestaurantMenuDate::create([
                'month' => $validatedData['month'],
                'year' => $validatedData['year'],
            ]);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }

        // 성공 메시지
        return response()->json(['message' => '식단표 날짜 저장 완료']);
    }



    /**
     * @OA\Post (
     * path="/api/restaurant/menu",
     * tags={"식수"},
     * summary="식단표 업로드",
     * description="바디에 엑셀 파일 담아서 보내면 됩니다.",
     *     @OA\RequestBody(
     *         description="학생 식사 신청 정보",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema (
     *                 @OA\Property (property="excel_file", type="file", description="식단표 엑셀 파일", example="1월.xsl"),
     *             )
     *         )
     *     ),
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function import(Request $request)
    {
        try{
            $excel_file = $request->file('excel_file');
            $excel_file->store('excels');
            Excel::import(new RestaurantMenuImport, $excel_file);
            return response()->json(['message' => '식단표 저장 완료'], 200);
        }catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
        
    }


/**
     * @OA\Get (
     * path="/api/restaurant/menu/get/m",
     * tags={"식수"},
     * summary="식수 식단표 1달치 가져오기",
     * description="식수 식단표 1달치를 가져옵니다",
     *     @OA\RequestBody(
     *         description="받고싶은 날짜(년,월)",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="month", type="string", description="월", example="03"),
 *                     @OA\Property (property="year", type="string", description="년", example="23"),
     *             )
     *         )
     *     ),
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function getMonthMenu(Request $request)
    {
        $month = RestaurantMenuDate::where('year', $request->year)->where('month', $request->month)->first();
        try {
            $monthMenus = RestaurantMenu::where('date_id', $month->id)->get();
            return response()->json(['month_menus' => $monthMenus]);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }



    /**
     * @OA\Get (
     * path="/api/restaurant/menu/get/d",
     * tags={"식수"},
     * summary="식수 식단표 하루치 가져오기",
     * description="식수 식단표 하루치를 가져옵니다",
     *     @OA\RequestBody(
     *         description="받고싶은 날짜(년,월,일)",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="date", type="string", description="년-월-일", example="2023-11-14"),
     *             )
     *         )
     *     ),
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function getDayMenu(Request $request)
    {
        try {
            $monthMenus = RestaurantMenu::where('date', $request->date)->get();
            return response()->json(['month_menus' => $monthMenus]);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }
}
