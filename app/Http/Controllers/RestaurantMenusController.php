<?php

namespace App\Http\Controllers;

use App\Imports\RestaurantMenuImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class RestaurantMenusController extends Controller
{
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
        $excel_file = $request->file('excel_file');
        $excel_file->store('excels');
        Excel::import(new RestaurantMenuImport, $excel_file);
        return response()->json(['message' => 'Excel data has been imported successfully'], 200);
    }
}
