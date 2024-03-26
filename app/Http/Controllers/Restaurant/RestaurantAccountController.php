<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\RestaurantAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RestaurantAccountController extends Controller
{
    /**
     * @OA\Post (
     * path="/api/restaurant/account",
     * tags={"식당 계좌"},
     * summary="식당 계좌 저장",
     * description="식당 계좌 저장을 처리합니다",
     *    
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function store()
    {
        try {
            RestaurantAccount::create([
                'account' => '은행명 (번호)12345678901234 예금주명',
                'bank_name' => '은행명',
                'name' => '예금주명',
            ]);
            return response()->json(['message' => '식당 계좌 저장 완료'], 200);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }



    /**
     * @OA\patch (
     * path="/api/restaurant/account/set",
     * tags={"식당 계좌 수정"},
     * summary="식당 계좌 수정",
     * description="식당 계좌 수정을 처리합니다",
     *     @OA\RequestBody(
     *         description="식당 계좌 정보",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="account", type="string", description="계좌", example="계좌번호"),
     *                 @OA\Property (property="bank_name", type="string", description="은행명", example="은행명"),
     *                 @OA\Property (property="name", type="string", description="예금주명", example="예금주명"),
     *             )
     *         )
     *     ),
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function update(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'account' => 'required|string',
                'bank_name' => 'required|string',
                'name' => 'required|string',
            ]);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }

        try {
            $restaurantAccount = RestaurantAccount::first();
            $restaurantAccount->update([
                'account' => $validatedData['account'],
                'bank_name' => $validatedData['bank_name'],
                'name' => $validatedData['name'],
            ]);
            return response()->json(['message' => '식당 계좌 수정 완료'], 200);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }



    /**
     * @OA\Get (
     * path="/api/restaurant/account/show",
     * tags={"식당 계좌 조회"},
     * summary="식당 계좌 조회",
     * description="식당 계좌 조회를 처리합니다",
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function show()
    {
        try {
            $restaurantAccount = RestaurantAccount::first();
            return response()->json(['data' => $restaurantAccount], 200);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }
}
