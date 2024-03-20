<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\DestroyException;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\TokenService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function __construct(protected TokenService $tokenService)
    {
    }

    /**
     * @OA\Get (
     *     path="/api/admin",
     *     tags={"관리자"},
     *     summary="관리자 정보",
     *     description="현재 인증된 관리자의 정보를 반환",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function admin(): \Illuminate\Http\JsonResponse
    {
        return response()->json(['admin' => auth('admins')->user()]);
    }

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
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function register(Request $request): \Illuminate\Http\JsonResponse
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
            'approved'     => true,
        ]);

        $admin['admin_id'] = $admin['id'];
        unset($admin['id']);

        if(!$admin) return response()->json(['error' => '관리자 회원가입에 실패하였습니다.'],500);

        return response()->json($admin, 201);
    }

    /**
     * @OA\Delete (
     *     path="/api/admin",
     *     tags={"관리자"},
     *     summary="탈퇴(본인)",
     *     description="관리자 본인이 계정 탈퇴 시 사용합니다.",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Server Error"),
     * )
     */
    public function unregister(): \Illuminate\Http\JsonResponse
    {
        $adminId = auth('admins')->id();
        if (!Admin::destroy($adminId)) {
            throw new DestroyException('회원탈퇴에 실패하였습니다.');
        }

        return response()->json(['message' => '회원탈퇴 되었습니다.']);
    }

    /**
     * @OA\Delete (
     *     path="/api/admin/master/{id}",
     *     tags={"관리자"},
     *     summary="탈퇴(마스터)",
     *     description="마스터 관리자가 다른 관리자를 탈퇴시킬 때 사용합니다.",
     *     @OA\Parameter(
     *          name="id",
     *          description="삭제할 관리자의 아이디",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function unregisterMaster(string $id): \Illuminate\Http\JsonResponse
    {
        if (!Admin::destroy($id)) {
            throw new DestroyException('회원탈퇴에 실패하였습니다.');
        }

        return response()->json(['message' => '회원탈퇴 되었습니다.']);
    }

    /**
     * @OA\Post (
     *     path="/api/admin/login",
     *     tags={"관리자"},
     *     summary="로그인",
     *     description="관리자 로그인 시 사용합니다.",
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
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     * @throws ValidationException
     */
    public function login(Request $request): \Illuminate\Http\JsonResponse
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

        if (!$token = $this->tokenService->createAccessToken('admins', $credentials)) {
            return response()->json(['error' => '관리자의 이메일 혹은 비밀번호가 올바르지 않습니다.'], 401);
        }
        $refreshToken = $this->tokenService->createRefreshToken('admins', $credentials);

        return response()->json([
            'user' => auth('admins')->user(),
            'access_token' => $token,
            'refresh_token' => $refreshToken,
        ]);
    }

    /**
     * @OA\Post (
     *     path="/api/admin/logout",
     *     tags={"관리자"},
     *     summary="로그아웃",
     *     description="관리자 로그아웃",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Server Error"),
     * )
     */
    public function logout(): \Illuminate\Http\JsonResponse
    {
        auth('admins')->logout();
        return response()->json(['success' => '성공적으로 로그아웃 되었습니다.']);
    }

    /**
     * @OA\Patch (
     *     path="/api/admin/privilege",
     *     tags={"관리자"},
     *     summary="권한 변경",
     *     description="관리자의 서비스 권한 변경 시 사용합니다.",
     *     @OA\RequestBody(
     *         description="관리자 권한 변경을 위한 값 및 아이디",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *             @OA\Property (property="admin_id", type="integer", description="관리자 아이디", example=1),
     *             @OA\Property (property="salon_privilege", type="boolean", description="미용실 권한", example=true),
     *             @OA\Property (property="admin_privilege", type="boolean", description="행정 권한", example=true),
     *             @OA\Property (property="restaurant_privilege", type="boolean", description="식당 권한", example=true),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="ModelNotFoundException"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function privilege(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'admin_id'              => 'required|numeric',
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
            $admin = Admin::findOrFail($validated['admin_id']);
        } catch(ModelNotFoundException) {
            return response()->json(['error' => $this->modelExceptionMessage], 404);
        }

        unset($validated['admin_id']);

        foreach($validated as $key => $value) {
            $admin->$key = $value;
        }

        if(!$admin->save()) {
            return response()->json(['error' => '관리자 권한 변경에 실패하였습니다.'], 500);
        }

        return response()->json(['message' => '관리자 권한이 변경되었습니다.']);
    }

    /**
     * @OA\Patch (
     *     path="/api/admin/approve",
     *     tags={"관리자"},
     *     summary="회원가입 승인",
     *     description="관리자 회원가입 승인 (거부는 회원 탈퇴 API 사용 해주세요)",
     *     @OA\RequestBody(
     *         description="아이디",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *            @OA\Schema (
     *              @OA\Property (property="admin_id", type="number", description="권한을 부여할 관리자의 아이디", example=1),
     *            )
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="ModelNotFoundException"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function approveRegistration(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'admin_id' => 'required|numeric',
            ]);
        } catch(ValidationException $validationException) {
                $errorStatus = $validationException->status;
                $errorMessage = $validationException->getMessage();
                return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        try {
            $admin = Admin::findOrFail($validated['admin_id']);
        } catch(ModelNotFoundException) {
            return response()->json(['error'=> $this->modelExceptionMessage], 404);
        }

        $admin->approved = true;

        if(!$admin->save()) {
            return response()->json(['error' => '관리자 승인에 실패하였습니다.'], 500);
        }
        return response()->json(['message' => '관리자가 승인되었습니다.']);
    }

    /**
     * @OA\Patch (
     *     path="/api/admin",
     *     tags={"관리자"},
     *     summary="개인정보 수정",
     *     description="관리자 개인정보 수정 시 사용합니다.",
     *     @OA\RequestBody(
     *         description="수정할 관리자 정보",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                  @OA\Property (property="name", type="string", description="변경할 이름", example="hyun"),
     *                  @OA\Property (property="phone_number", type="string", description="변경할 휴대폰 번호", example="01012345678"),
     *                  @OA\Property (property="current_password", type="string", description="이전 비밀번호", example="asdf321"),
     *                  @OA\Property (property="new_password", type="string", description="변경할 비밀번호", example="asdf123"),
     *             )
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="ModelNotFoundException"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function updateProfile(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'name'             => 'string',
                'phone_number'     => 'string|unique:admins',
                'current_password' => 'current_password',
                'new_password'     => 'string|required_with:current_password',
            ]);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        $adminId = auth('admins')->id();

        try {
            $admin = Admin::findOrFail($adminId);
        } catch(ModelNotFoundException) {
            return response()->json(['error' => $this->modelExceptionMessage], 404);
        }

        unset($validated['current_password']);

        foreach($validated as $key => $value) {
            if($key == 'new_password') {
                $admin->password = Hash::make($validated['new_password']);
            } else {
                $admin->$key = $value;
            }
        }

        if(!$admin->save()) return response()->json(['error' => '관리자 정보 수정에 실패하였습니다.'], 500);

        return response()->json(['message' => '관리자 정보가 수정되었습니다.']);
    }

    /**
     * @OA\Get (
     *     path="/api/admin/verify-email/{id}",
     *     tags={"관리자"},
     *     summary="이메일 중복 확인",
     *     description="관리자 회원가입 시 이메일 중복 확인을 체크할 때 사용합니다.",
     *      @OA\Parameter(
     *            name="email",
     *            description="중복을 확인할 관리자의 이메일",
     *            required=true,
     *            in="path",
     *            @OA\Schema(type="string"),
     *        ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function verifyUniqueAdminEmail(string $email): \Illuminate\Http\JsonResponse
    {
        $rules = [
            'email' => 'required|email|unique:admins,email'
        ];
        $validator = Validator::make(['email' => $email], $rules);

        try {
            $validator->validate();
        } catch(ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
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
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function verifyPassword(Request $request): \Illuminate\Http\JsonResponse
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

        // TODO: 오류나는지 체크 해야함
        if(!Hash::check($validated['password'], auth('admins')->user()->getAuthPassword())) {
            return response()->json(['error' => '비밀번호가 일치하지 않습니다.'], 500);
        }

        return response()->json(['success' => '비밀번호가 일치합니다.']);
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
     *     @OA\Response(response="404", description="ModelNotFoundException"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function findEmail(Request $request): \Illuminate\Http\JsonResponse
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
        } catch (ModelNotFoundException) {
            return response()->json(['error' => $this->modelExceptionMessage], 404);
        }
        return response()->json(['admin' => $admin]);
    }

    /**
     * @OA\Get (
     *     path="/api/admin/list",
     *     tags={"관리자"},
     *     summary="승인 혹은 미승인 관리자 목록",
     *     description="파라미터 값에 맞는 관리자를 admins 배열에 반환",
     *     @OA\RequestBody(
     *     description="승인 관리자 조회의 경우 approved, 미승인 관리자 조회의 경우 unapproved, 전체 조회 시에는 body 없이 요청만",
     *     required=false,
     *         @OA\MediaType(
     *         mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="type", type="string", description="승인 미승인 여부", example="approved"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function adminList(Request $request): \Illuminate\Http\JsonResponse
    {
        $typeRule = ['approved', 'unapproved'];

        try {
            $validated = $request->validate([
                'type' => ['string', Rule::in($typeRule)],
            ]);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        $admins = Admin::all();

        if(isset($validated['type'])) {
            if($validated['type'] == 'unapproved') {
                $admins = $admins->where('approved', '=', false)->values();
            } else if($validated['type'] == 'approved') {
                $admins = $admins->where('approved','=', true)->values();
            }
        }

        return response()->json(['admins' => $admins]);
    }
}
