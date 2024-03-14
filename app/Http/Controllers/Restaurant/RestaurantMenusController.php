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
                'week' => 'required|string',
            ]);
        } catch (ValidationException $exception) {
            // 유효성 검사 실패시 애러 메세지
            return response()->json(['error' => $exception->getMessage()], 422);
        }
       
        try {
            // 데이터베이스에 저장
            RestaurantMenuDate::create([
                'month' => $validatedData['month'],
                'year' => date('Y'),
                'week' => $validatedData['week'],
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
     * path="/api/restaurant/menu/get/w",
     * tags={"식수"},
     * summary="식단표 그 달의 모든 주차 가져오기",
     * description="식단표 달의 모든 주차 가져오기",
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
    public function getWeek(Request $request)
    { 
        try {
            $month_ids = RestaurantMenuDate::where('year', $request->year)
                                            ->where('month', $request->month)
                                            ->pluck('id');
                                            Log::info('먼스 아이디: ' .$month_ids); 
            $weeks = [];
            foreach ($month_ids as $month_id) {
                $week = RestaurantMenuDate::find($month_id)->week;
                $weeks[] = $week;
            }
                
            return response()->json(['month_menus' => $weeks]);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }



/**
     * @OA\Get (
     *     path="/api/restaurant/menu/get/w/{id}",
     *     tags={"식수"},
     *     summary="식단표 1주치 가져오기",
     *     description="식단표 1주치 가져오기",
     *     @OA\Parameter(
     *           name="id",
     *           description="가져올 식단표의 date_id 아이디",
     *           required=true,
     *           in="path",
     *           @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function getWeekMenu($id)
    {
        try {
            $weekDay = RestaurantMenu::where('date_id', $id)->get();
            return response()->json(['week_menus' => $weekDay]);
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
            $monthDay = RestaurantMenu::where('date', $request->date)->get();
            return response()->json(['month_menus' => $monthDay]);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }


 
    public function deleteDate($id)// year, month 값으로 받을지
    {
        try {
            $menuDate = RestaurantMenuDate::findOrFail($id);
            $menuDate->delete();
            return response()->json(['message' => '식단표 날짜가 삭제되었습니다.']);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }
    
 /**
     * @OA\Delete (
     *     path="/api/restaurant/menu/d/{id}",
     *     tags={"식수"},
     *     summary="식단표 삭제",
     *     description="식단표 삭제",
     *     @OA\Parameter(
     *           name="id",
     *           description="삭제할 식단표의 date_id 아이디",
     *           required=true,
     *           in="path",
     *           @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function deleteMenu($id)//삭제할 date_id
    {
        try {
            RestaurantMenu::where('date_id',$id)->delete();
            return response()->json(['message' => '식단표가 삭제되었습니다.']);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }
}
