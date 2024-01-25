<?php

namespace App\Http\Controllers;

use App\Exceptions\DestroyException;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    /**
     * @OA\Post (
     *     path="/api/admin/register",
     *     tags={"관리자"},
     *     summary="관리자 회원가입",
     *     description="관리자 회원가입",
     *     @OA\RequestBody(
     *         description="관리자 회원가입 정보",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="name", type="string", description="사용자 이름", example="관리자"),
     *                 @OA\Property (property="phone_number", type="string", description="전화번호", example="01012345678"),
     *                 @OA\Property (property="email", type="string", description="이메일", example="admin@gmail.com"),
     *                 @OA\Property (property="password", type="string", description="비밀번호", example="admin123"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function register(Request $request)
    {
        try {
            $validated = $request->validate($this->adminValidateRules);
        } catch(ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        $admin = Admin::create([
            'name'         => $validated['name'],
            'phone_number' => $validated['phone_number'],
            'email'        => $validated['email'],
            'password'     => Hash::make($validated['password']),
        ]);

        if(!$admin) return response()->json(['error' => 'Failed to register'],500);

        return response()->json($admin);
    }

    /**
     * @OA\Delete (
     *     path="/api/admin/unregister/{id}",
     *     tags={"관리자"},
     *     summary="관리자 탈퇴",
     *     description="관리자 탈퇴",
     *      @OA\Parameter(
     *            name="id",
     *            description="탈퇴할 관리자의 아이디",
     *            required=true,
     *            in="path",
     *            @OA\Schema(type="integer"),
     *        ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function unregister(string $id)
    {
        if (!Admin::destroy($id)) {
            throw new DestroyException('Failed to destroy user');
        }

        return response()->json(['message'=>'Destroy user successfully']);
    }

    /**
     * @OA\Post (
     *     path="/api/admin/login",
     *     tags={"관리자"},
     *     summary="관리자 로그인",
     *     description="관리자 로그인. 요청 시 /sanctum/csrf-coocie 경로로 먼저 요청 보내고 로그인 시도하기",
     *     @OA\RequestBody(
     *         description="관리자 로그인",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="email", type="string", description="이메일", example="admin@gmail.com"),
     *                 @OA\Property (property="password", type="string", description="비밀번호", example="admin123"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
        } catch(ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        if(Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return response()->json(['message' => 'logged in successfully']);
        }

        return response()->json(['error' => 'The provided credentials do not match our records.'], 401);
    }

    /**
     * @OA\Post (
     *     path="/api/admin/logout",
     *     tags={"관리자"},
     *     summary="관리자 로그아웃",
     *     description="관리자 로그아웃",
     *     @OA\RequestBody(
     *         description="관리자 로그아웃",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function logout(Request $request) {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json(['message' => 'logout successfully']);
    }
}
