<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\DestroyException;
use App\Http\Controllers\Controller;
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
     *     path="/api/admin",
     *     tags={"관리자"},
     *     summary="회원가입",
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
     *     path="/api/admin/{id}",
     *     tags={"관리자"},
     *     summary="탈퇴",
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
     *     summary="로그인",
     *     description="관리자 로그인. 요청 시 /sanctum/csrf-coocie 경로로 먼저 요청 보내고 로그인 시도하기",
     *     @OA\RequestBody(
     *         description="관리자 로그인을 위한 정보",
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
            return Auth::user();
        }

        return response()->json(['error' => 'The provided credentials do not match our records.'], 401);
    }

    /**
     * @OA\Post (
     *     path="/api/admin/logout",
     *     tags={"관리자"},
     *     summary="로그아웃",
     *     description="관리자 로그아웃",
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
     *     summary="권한 변경",
     *     description="관리자 권한 변경",
     *     @OA\RequestBody(
     *         description="관리자 권한 변경을 위한 값 및 아이디",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *             @OA\Property (property="id", type="number", description="권한을 부여할 유저 아이디", example=1),
     *             @OA\Property (property="salon_privilege", type="boolean", description="미용실 권한", example=true),
     *             @OA\Property (property="admin_privilege", type="boolean", description="행정 권한", example=true),
     *             @OA\Property (property="restaurant_privilege", type="boolean", description="식당 권한", example=true),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function privilege(Request $request) {
        try {
            $validated = $request->validate([
                'id'               => 'required|numeric',
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
            $admin = Admin::findOrFail($validated['id']);
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

        return response()->json(['message' => 'update privilege successfully']);
    }

    /**
     * @OA\Patch (
     *     path="/api/admin/approve",
     *     tags={"관리자"},
     *     summary="회원가입 승인",
     *     description="관리자 회원가입 승인 (거부 시 계정 정보 삭제 됨)",
     *     @OA\RequestBody(
     *         description="아이디 및 승인 여부",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *            @OA\Schema (
     *              @OA\Property (property="admin_id", type="number", description="권한을 부여할 관리자의 아이디", example=1),
     *              @OA\Property (property="approve", type="boolean", description="관리자 회원가입 승인 여부", example=false),
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
            $admin = Admin::findOrFail($validated['admin_id']);
        } catch(ModelNotFoundException $modelException) {
            $errorStatus = $modelException->status;
            $errorMessage = $modelException->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        $admin->approved = true;

        if(!$admin->save()) {
            return response()->json(['error' => 'failed to update approve status'], 500);
        }
        return response()->json(['message' => 'update approve status successfully']);
    }

    /**
     * @OA\Patch (
     *     path="/api/admin",
     *     tags={"관리자"},
     *     summary="개인정보 수정",
     *     description="관리자 개인정보 수정",
     *     @OA\RequestBody(
     *         description="수정할 관리자 정보",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                  @OA\Property (property="admin_id", type="number", description="정보를 수정할 관리자의 아이디", example=1),
     *                  @OA\Property (property="name", type="string", description="변경할 이름", example="hyun"),
     *                  @OA\Property (property="phone_number", type="string", description="변경할 휴대폰 번호", example="01012345678"),
     *                  @OA\Property (property="password", type="string", description="변경할 비밀번호", example="asdf123"),
     *             )
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function updateProfile(Request $request)
    {
        try {
            $validated = $request->validate([
                'admin_id'      => 'required|numeric', // 수정할 유저의 아이디
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
            $admin = Admin::findOrFail($validated['admin_id']);
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
     * @OA\POST (
     *     path="/api/admin/verify-email/{email}",
     *     tags={"관리자"},
     *     summary="이메일 중복 확인",
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
    public function verifyUniqueEmail(Request $request)
    {
        try {
            $validated = $request->validate([
                'required|email',
            ]);
        } catch(ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        $admin = Admin::where('email', $validated['email'])->first();

        if(!empty($admin)) {
            return response()->json(['check' => false]);
        }
        return response()->json(['check' => true]);
    }

    /**
     * @OA\Post (
     *     path="/api/admin/verify-password",
     *     tags={"관리자"},
     *     summary="PW 체크",
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

        if(!Hash::check($validated['password'], $request->user()->password)) return false;

        return true;
    }
    /**
     * @OA\Post (
     *     path="/api/admin/find-email",
     *     tags={"관리자"},
     *     summary="이메일 찾기",
     *     description="회원가입 시 입력한 이름과 전화번호를 통하여 일치하는 값을 가진 이메일을 찾음",
     *     @OA\RequestBody(
     *         description="name & phone_number(without hyphen)",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="name", type="string", description="이름", example="testname"),
     *                 @OA\Property (property="phone_number", type="string", description="휴대폰 번호", example="01012345678"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="Model Not Found(해당 하는 값이 없음)"),
     *     @OA\Response(response="422", description="Unprocessable Content(Request body에 올바르게 값을 입력했는지 확인)"),
     * )
     */
    public function findEmail(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string',
                'phone_number' => 'required|string',
            ]);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        try {
            $admin = Admin::where('phone_number', $validated['phone_number'])->where('name', $validated['name'])->firstOrFail();
        } catch (ModelNotFoundException $modelNotFoundException) {
            $errorMessage = $modelNotFoundException->getMessage();
            return response()->json(['error'=>$errorMessage], 404);
        }
        return response()->json(['admin' => $admin]);
    }
    /**
     * @OA\Get (
     *     path="/api/admin",
     *     tags={"관리자"},
     *     summary="승인 혹은 미승인 관리자 목록",
     *     description="파라미터 값에 맞는 관리자를 admins 배열에 반환",
     *     @OA\RequestBody(
     *     description="승인 관리자 조회의 경우 true, 미승인 관리자 조회의 경우 false, 전체 조회 시에는 body 없이 요청만",
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
    public function adminList(Request $request)
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

        if(!isset($validated['type'])) return response()->json(['admins' => Admin::all()]);

        return response()->json(['admins' => Admin::where('approved', $validated['type'])->get()]);
    }
}
