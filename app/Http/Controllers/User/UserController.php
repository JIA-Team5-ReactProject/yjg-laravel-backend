<?php

namespace App\Http\Controllers\User;

use App\Exceptions\DestroyException;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class UserController extends Controller
{
    /**
     * @OA\Get (
     *     path="/api/user/login",
     *     tags={"학생"},
     *     summary="로그인",
     *     description="유저 Google 로그인",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function googleRegisterOrLogin(Request $request)
    {
        $gUser = Socialite::driver('google')->stateless()->user();
        $user = User::updateOrCreate([
            'email' => $gUser->email,
        ], [
            'name' => $gUser->name,
            'approved' => true,
        ]);
        $data = [
            'token' => $user->createToken(env('LOGIN_TOKEN_NAME'))->plainTextToken,
            'user' => $user,
        ];

        return response()->json(['data' => $data]);
    }


    /**
     * @OA\Get (
     *     path="/api/user/logout",
     *     tags={"학생"},
     *     summary="로그아웃",
     *     description="유저 Google 로그아웃",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function logout(Request $request)
    {
        $response = $request->user()->tokens()->delete();

        if(!$response) return response()->json(['error' => 'Failed to logout'], 500);

        return response()->json(['message' => $response]);
    }


    /**
     * @OA\Patch (
     *     path="/api/user",
     *     tags={"학생"},
     *     summary="개인정보 수정",
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

    /**
     * @OA\Post (
     *     path="/api/user/foreigner",
     *     tags={"학생"},
     *     summary="회원가입",
     *     description="유학생 회원가입",
     *     @OA\RequestBody(
     *         description="회원가입 정보",
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
    public function foreignerRegister(Request $request)
    {
        try {
            $validated = $request->validate($this->userValidateRules);
        } catch(ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        $admin = User::create([
            'name'         => $validated['name'],
            'phone_number' => $validated['phone_number'],
            'email'        => $validated['email'],
            'password'     => Hash::make($validated['password']),
        ]);

        if(!$admin) return response()->json(['error' => 'Failed to register'],500);

        return response()->json($admin, 201);
    }

    /**
     * @OA\Post (
     *     path="/api/user/foreigner/login",
     *     tags={"학생"},
     *     summary="로그인",
     *     description="유학생 로그인",
     *     @OA\RequestBody(
     *         description="회원가입 정보",
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

    public function foreignerLogin(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
        } catch(ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        try {
            $user = User::where('email', $credentials['email'])->first();
        } catch(ModelNotFoundException $modelNotFoundException) {
            $errorStatus = $modelNotFoundException->status;
            $errorMessage = $modelNotFoundException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $user->createToken('email')->plainTextToken;
    }

    /**
     * @OA\Patch (
     *     path="/api/user/approve",
     *     tags={"학생"},
     *     summary="회원가입 승인",
     *     description="유학생 회원가입 승인 (거부 시 계정 정보 삭제 됨)",
     *     @OA\RequestBody(
     *         description="아이디 및 승인 여부",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *            @OA\Schema (
     *              @OA\Property (property="admin_id", type="number", description="권한을 부여할 유학생의 아이디", example=1),
     *              @OA\Property (property="approve", type="boolean", description="유학생 회원가입 승인 여부", example=false),
     *            )
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function approveRegistration(Request $request)
    {
        try {
            $validated = $request->validate([
                'admin_id' => 'required|numeric',
                'approve'  => 'required|boolean',
            ]);
        } catch(ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        try {
            $user = User::findOrFail($validated['admin_id']);
        } catch(ModelNotFoundException $modelException) {
            $errorStatus = $modelException->status;
            $errorMessage = $modelException->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        $user->approved = true;

        if(!$user->save()) {
            return response()->json(['error' => 'failed to update approve status'], 500);
        }
        return response()->json(['message' => 'update approve status successfully']);
    }

    /**
     * @OA\Get (
     *     path="/api/user",
     *     tags={"학생"},
     *     summary="승인 혹은 미승인 학생 목록",
     *     description="파라미터 값에 맞는 학생을 users 배열에 반환",
     *     @OA\RequestBody(
     *     description="승인 학생 조회의 경우 true, 미승인 학생 조회의 경우 false, 전체 조회 시에는 body 없이 요청만",
     *     required=false,
     *         @OA\MediaType(
     *         mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="type", type="boolean", description="승인 미승인 여부", example=true),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Server Error"),
     * )
     */
    public function userList(Request $request)
    {
        try {
            $validated = $request->validate([
                'type' => 'boolean',
            ]);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        if(!isset($validated['type'])) $users = User::all();
        else $users = User::where('approved' , $validated['type'])->get();

        return response()->json(['users' => $users]);
    }

    /**
     * @OA\Delete (
     *     path="/api/user/{id}",
     *     tags={"학생"},
     *     summary="탈퇴",
     *     description="일반 학생 및 유학생 탈퇴",
     *      @OA\Parameter(
     *            name="id",
     *            description="탈퇴할 학생, 유학생의 아이디",
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
