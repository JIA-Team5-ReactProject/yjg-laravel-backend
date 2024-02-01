<?php

namespace App\Http\Controllers;

use App\Exceptions\DestroyException;
use App\Models\Admin;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

        return response()->json($admin, 201);
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
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json(['message' => 'logout successfully']);
    }

    /**
     * @OA\Patch (
     *     path="/api/admin/privilege",
     *     tags={"관리자"},
     *     summary="관리자 권한 변경",
     *     description="관리자 권한 변경",
     *     @OA\RequestBody(
     *         description="관리자 권한 변경",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         ),
     *         @OA\Schema (
     *             @OA\Property (property="user_id", type="number", description="권한을 부여할 유저 아이디", example=1),
     *             @OA\Property (property="salon_privilege", type="boolean", description="미용실 권한", example=true),
     *             @OA\Property (property="admin_privilege", type="boolean", description="행정 권한", example=true),
     *             @OA\Property (property="restaurant_privilege", type="boolean", description="식당 권한", example=true),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function privilege(Request $request) {
        try {
            $validated = $request->validate([
                'admin_id'               => 'required|number',
                'salon_privilege'       => 'required|boolean',
                'admin_privilege'       => 'required|boolean',
                'restaurant_privilege'  => 'required|boolean',
            ]);
        } catch(ValidationException $validateException) {
            $errorStatus = $validateException->status;
            $errorMessage = $validateException->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        try {
            $admin = Admin::firstOrFail($validated['admin_id']);
        } catch(ModelNotFoundException $modelException) {
            $errorStatus = $modelException->status;
            $errorMessage = $modelException->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        foreach($validated as $key => $value) {
            $admin->$key = $value;
        }

        if(!$admin->save()) {
            return response()->json(['failed to update privilege'], 500);
        }

        return response()->json(['update privilege successfully']);
    }

    /**
     * @OA\Patch (
     *     path="/api/admin/approve",
     *     tags={"관리자"},
     *     summary="관리자 회원가입 승인",
     *     description="관리자 회원가입 승인 (거부 시 계정 정보 삭제 됨)",
     *     @OA\RequestBody(
     *         description="아이디 및 승인 여부",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         ),
     *         @OA\Schema (
     *             @OA\Property (property="user_id", type="number", description="권한을 부여할 유저 아이디", example=1),
     *             @OA\Property (property="approve", type="boolean", description="관리자 회원가입 승인 여부", example=false),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function approveRegistration(Request $request)
    {
        try {
            $validated = $request->validate([
                'admin_id' => 'required|number',
                'approve'  => 'required|boolean|accepted', // 반드시 참을 의미하는 값을 가져야 함
            ]);
        } catch(ValidationException $validationException) {
                $errorStatus = $validationException->status;
                $errorMessage = $validationException->getMessage();
                return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        try {
            $admin = Admin::firstOrFail($validated['admin_id']);
        } catch(ModelNotFoundException $modelException) {
            $errorStatus = $modelException->status;
            $errorMessage = $modelException->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        $admin->approved = true;

        if(!$admin->save()) {
            return response()->json(['failed to update approve status'], 500);
        }
        return response()->json(['update approve status successfully']);
    }

    /**
     * @OA\Patch (
     *     path="/api/admin/update",
     *     tags={"관리자"},
     *     summary="관리자 개인정보 수정",
     *     description="관리자 개인정보 수정",
     *     @OA\RequestBody(
     *         description="수정할 관리자 정보",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         ),
     *         @OA\Schema (
     *             @OA\Property (property="user_id", type="number", description="정보를 수정할 유저의 아이디", example=1),
     *             @OA\Property (property="name", type="string", description="변경할 이름", example="hyun"),
     *             @OA\Property (property="phone_number", type="string", description="변경할 휴대폰 번호", example="01012345678"),
     *             @OA\Property (property="password", type="string", description="변경할 비밀번호", example="asdf123"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function updateProfile(Request $request)
    {
        try {
            $validated = $request->validate([
                'admin_id'      => 'required|number', // 수정할 유저의 아이디
                'name'          => 'required|string',
                'phone_number'  => 'required|string|unique:admins',
                'password'      => 'required|string|',
            ]);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        // admin_id에 해당하는 모델 검색
        try {
            $admin = Admin::firstOrFail($validated['admin_id']);
        } catch(ModelNotFoundException $modelException) {
            $errorStatus = $modelException->status;
            $errorMessage = $modelException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        $admin->name = $validated['name'];
        $admin->phone_number = $validated['phone_number'];
        $admin->password = Hash::make($validated['password']);

        if(!$admin->save()) return response()->json(['error' => 'Failed to update profile'], 500);

        return response()->json(['message' => 'Update profile successfully']);
    }

    /**
     * @OA\Get (
     *     path="/api/admin/verify-email/{email}",
     *     tags={"관리자"},
     *     summary="관리자 이메일 중복 확인",
     *     description="관리자 이메일 중복 확인",
     *      @OA\Parameter(
     *            name="id",
     *            description="중복을 확인할 관리자의 이메일",
     *            required=true,
     *            in="path",
     *            @OA\Schema(type="string"),
     *        ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function verifyUniqueEmail(string $email)
    {
        $admin = Admin::where('email', $email)->first();
        if(!empty($admin)) {
            return response()->json(['check' => false]);
        }
        return response()->json(['check' => true]);
    }

    /**
     * @OA\Get (
     *     path="/api/admin/verify-password",
     *     tags={"관리자"},
     *     summary="관리자 PW 체크",
     *     description="관리자 회원정보 수정 페이지 접속 시 PW 체크",
     *     @OA\RequestBody(
     *         description="PW",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="password", type="string", description="비밀번호", example="admin123"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function verifyPassword(Request $request)
    {
        try {
            $validated = $request->validate([
                'password' => 'required|string',
            ]);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }
        $user = Auth::user(); // 현재 인증된 유저

        if(!Hash::check($validated['password'], $user->getAuthPassword())) return false;

        return true;
    }
}
