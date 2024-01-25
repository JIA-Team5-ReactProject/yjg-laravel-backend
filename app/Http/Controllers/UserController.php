<?php

namespace App\Http\Controllers;

use App\Exceptions\DestroyException;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * @OA\Post (
     *     path="/api/register",
     *     tags={"유저"},
     *     summary="회원가입",
     *     description="회원가입 시 유저 정보 저장",
     *     @OA\RequestBody(
     *         description="유저 회원가입 정보",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="student_id", type="string", description="학번", example="2401234"),
     *                 @OA\Property (property="name", type="string", description="사용자 이름", example="엄준식"),
     *                 @OA\Property (property="phone_number", type="string", description="전화번호", example="01012345678"),
     *                 @OA\Property (property="email", type="string", description="이메일", example="umjinsik@gmail.com"),
     *                 @OA\Property (property="password", type="string", description="비밀번호", example="umjunsik123"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail")
     * )
     */
    public function register(Request $request)
    {
        try {
            $validated = $request->validate($this->userValidateRules);
        } catch(ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        $user = User::create([
           'student_id'   => $validated['student_id'],
           'name'         => $validated['name'],
           'phone_number' => $validated['phone_number'],
           'email'        => $validated['email'],
           'password'     => Hash::make($validated['password']),
        ]);

        return response()->json($user);
    }

    /**
     * @OA\Delete (
     *     path="/api/unregister/{id}",
     *     tags={"유저"},
     *     summary="회원탈퇴",
     *     description="회원탈퇴",
     *      @OA\Parameter(
     *            name="id",
     *            description="탈퇴할 유저의 아이디",
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
        if (!User::destroy($id)) {
            throw new DestroyException('Failed to destroy user');
        }

        return response()->json(['message'=>'Destroy user successfully']);
    }
}
