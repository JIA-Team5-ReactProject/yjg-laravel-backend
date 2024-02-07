<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class UserController extends Controller
{
    /**
     * @OA\Get (
     *     path="/api/user/login",
     *     tags={"유저"},
     *     summary="로그인",
     *     description="유저 Google 로그인",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function registerOrLogin(Request $request)
    {
        // 도메인 예외처리
        // 승인여부 확인하기
        $gUser = Socialite::driver('google')->stateless()->user();

        $user = User::updateOrCreate([
            'email' => $gUser->email,
        ], [
            'name' => $gUser->name,
        ]);
        $data = [
            'token' => $user->createToken(env('TOKEN_NAME'))->plainTextToken,
            'user' => $user,
        ];

        return response()->json(['data' => $data]);
    }


    /**
     * @OA\Get (
     *     path="/api/user/logout",
     *     tags={"유저"},
     *     summary="로그아웃",
     *     description="유저 Google 로그아웃",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function logout(Request $request)
    {
        $response = $request->user()->currentAccessToken()->delete();

        if(!$response) return response()->json(['error' => 'Failed to logout'], 500);

        return response()->json(['message' => $response]);
    }


    /**
     * @OA\Patch (
     *     path="/api/user/update",
     *     tags={"유저"},
     *     summary="유저 개인정보 수정",
     *     description="유저 개인정보 수정",
     *     @OA\RequestBody(
     *         description="수정할 유저의 정보",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                  @OA\Property (property="admin_id", type="number", description="정보를 수정할 유저의 아이디", example=1),
     *                  @OA\Property (property="name", type="string", description="변경할 이름", example="hyun"),
     *                  @OA\Property (property="phone_number", type="string", description="변경할 휴대폰 번호", example="01012345678"),
     *             )
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id'      => 'required|numeric', // 수정할 유저의 아이디
                'name'          => 'required|string',
                'phone_number'  => 'required|string|unique:admins',
            ]);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        // user_id에 해당하는 모델 검색
        try {
            $user = User::findOrFail($validated['user_id']);
        } catch(ModelNotFoundException $modelException) {
            $errorStatus = $modelException->status;
            $errorMessage = $modelException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        $user->name = $validated['name'];
        $user->phone_number = $validated['phone_number'];

        if(!$user->save()) return response()->json(['error' => 'Failed to update profile'], 500);

        return response()->json(['message' => 'Update profile successfully']);
    }

}
