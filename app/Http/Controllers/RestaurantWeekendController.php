<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException; // 예외 처리
use App\Models\RestaurantWeekend;

class RestaurantWeekendController extends Controller
{
    /**
     * @OA\Post (
     *     path="/api/restaurant/weekend",
     *     tags={"학생"},
     *     summary="식수 주말 신청",
     *     description="식수 주말 신청을 처리합니다",
     *         @OA\RequestBody(
     *             description="학생 식사 신청 정보",
     *             required=true,
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema (
     *                     @OA\Property (property="user_id", type="string", description="사용자 ID", example="1"),
     *                     @OA\Property (property="menu_type", type="string", description="식사유형", example="A"),
     *                     @OA\Property (property="sat", type="boolean", description="토요일", example="true"),
     *                     @OA\Property (property="sun", type="boolean", description="일요일", example="true"),
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
                'user_id' => 'required|exists:users,id',
                'menu_type' => 'required|string|in:A,B,C',
                'sat' => 'required|boolean',
                'sun' => 'required|boolean',
            ]);
        } catch (ValidationException $exception) {
            // 유효성 검사 실패시 애러 메세지
            return response()->json(['error' => $exception->getMessage()], 422);
        }

        try {
            // 데이터베이스에 저장
            $weekendMeal = RestaurantWeekend::create([
                'user_id' => $validatedData['user_id'],
                'menu_type' => $validatedData['menu_type'],
                'sat' => $validatedData['sat'],
                'sun' => $validatedData['sun'],
            ]);
        } catch (\Exception $exception) {
            // 데이터베이스 저장 실패시 애러 메세지
            //return response()->json(['error' => '데이터베이스에 저장하는 중에 오류가 발생했습니다.'], 500);
            return response()->json(['error' =>  $exception->getMessage()], 500);
        }

        // 완료 메시지 주기
        return response()->json(['message' => '주말 식수 신청이 완료되었습니다.']);
    }
}












// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use App\Models\RestaurantWeekend;

// class RestaurantWeekendController extends Controller
// {
//     public function store(Request $request)
//     {
//         //유효성 검사
//         $validatedData = $request->validate([
//             'user_id' => 'required|exists:users,id',
//             'menu_type' => 'required|string|in:A,B,C',
//             'sat' => 'required|boolean',
//             'sun' => 'required|boolean',
//         ]);

//         // 데이터베이스에 저장
//         $weekendMeal = RestaurantWeekend::create([
//             'user_id' => $validatedData['user_id'],
//             'menu_type' => $validatedData['menu_type'],
//             'sat' => $validatedData['sat'],
//             'sun' => $validatedData['sun'],
            
//         ]);

//         // 완료 메시지 주기
//         return response()->json(['message' => '주말 식수 신청이 완료되었습니다.']);
//     }
// }
