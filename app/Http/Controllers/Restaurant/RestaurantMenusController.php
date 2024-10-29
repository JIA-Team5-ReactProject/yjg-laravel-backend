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
     * path="/api/restaurant/menu",
     * tags={"식단표"},
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
    public function import(Request $request): \Illuminate\Http\JsonResponse
    {
        try{
            $excel_file = $request->file('excel_file');
            $excel_file->store('excels');
            Excel::import(new RestaurantMenuImport, $excel_file);
            return response()->json(['message' => __('messages.200')]);
        }catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }


/**석게이 왔다감
     * @OA\Get (
     * path="/api/restaurant/menu/get/year",
     * tags={"식단표"},
     * summary="년도 전부 가져오기",
     * description="년도 전부 가져오기",
     *
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function getyears(): \Illuminate\Http\JsonResponse
    {
        try {
            $years = RestaurantMenuDate::distinct()->pluck('year');

            return response()->json(['years' => $years]);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }



/**
     * @OA\Get (
     *     path="/api/restaurant/menu/get/w",
     *     tags={"식단표"},
     *     summary="식단표 1주치 가져오기",
     *     description="식단표 1주치 가져오기",
     *     @OA\RequestBody(
     *         description="가져올 식단표의 년도, 월",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema (
     *                 @OA\Property (property="year", type="string", description="년도", example="2024"),
     *                 @OA\Property (property="month", type="string", description="월", example="03"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function getWeekMenu(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $weekdata = RestaurantMenuDate::where('year', $request->year)
                                        ->where('month', $request->month)
                                        ->get();
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }

        try {
            // 주차별로 데이터를 그룹화합니다.
            $weekMenus = [];
            foreach ($weekdata->groupBy('week') as $datesInWeek) {
                // 각 주차의 날짜들에 대해 메뉴 데이터를 가져옵니다.
                $menusInWeek = [];
                foreach ($datesInWeek as $date) {
                    $menus = RestaurantMenu::where('date_id', $date->id)->get()->toArray();
                    // 해당 주의 메뉴가 없으면 생략합니다.
                    if (!empty($menus)) {
                        $menusInWeek = array_merge($menusInWeek, $menus);
                    }
                }
                // 해당 주의 메뉴가 있을 때만 추가합니다.
                if (!empty($menusInWeek)) {
                    $weekMenus[] = $menusInWeek;
                }
            }

            // 반환되는 JSON 구조를 좀 더 명확하게 처리합니다.
            return response()->json(['week_menus' => $weekMenus]);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }



    /**
     * @OA\Get (
     * path="/api/restaurant/menu/get/d",
     * tags={"식단표"},
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
    public function getDayMenu(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $monthDay = RestaurantMenu::where('date', $request->date)->get();
            return response()->json(['month_menus' => $monthDay]);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }


 /**
     * @OA\Delete (
     *     path="/api/restaurant/menu/d/{id}",
     *     tags={"식단표"},
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
    public function deleteMenu($id): \Illuminate\Http\JsonResponse//삭제할 date_id
    {
        try {
            RestaurantMenu::where('date_id',$id)->delete();
            return response()->json(['message' => __('messages.200')]);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }
}
