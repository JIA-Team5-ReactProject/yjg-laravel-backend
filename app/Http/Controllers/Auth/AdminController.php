<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\DestroyException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ResetPasswordService;
use App\Services\TokenService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function __construct(protected TokenService $tokenService)
    {
    }

    public function authorize($ability, $arguments = [User::class])
    {
        return Parent::authorize($ability, $arguments);
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

        $validated['admin'] = true;

        $admin = new User();

        foreach ($validated as $key => $value) {
            if($key == 'password') {
                $admin->$key = Hash::make($value);
                continue;
            }
            $admin->$key = $value;
        }

        if(!$admin->save()) return response()->json(['error' => '관리자 회원가입에 실패하였습니다.'],500);

        $admin['admin_id'] = $admin['id'];
        unset($admin['id']);

        return response()->json($admin, 201);
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
        try {
            $this->authorize('master');
        } catch (AuthorizationException) {
            return $this->denied();
        }

        if (!User::destroy($id)) {
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

        if (!$token = $this->tokenService->generateToken($credentials, 'access')) {
            return response()->json(['error' => '관리자의 이메일 혹은 비밀번호가 올바르지 않습니다.'], 401);
        }
        $refreshToken = $this->tokenService->generateToken($credentials, 'refresh');

        return response()->json([
            'user' => auth()->user(),
            'access_token' => $token,
            'refresh_token' => $refreshToken,
        ]);
    }

    /**
     * @OA\Post (
     *     path="/api/admin/login/web",
     *     tags={"관리자"},
     *     summary="로그인(웹)",
     *     description="관리자가 웹에서 로그인 시 사용합니다.",
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
     */
    public function webLogin(Request $request): \Illuminate\Http\JsonResponse
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

        if (!$token = $this->tokenService->generateToken($credentials, 'access')) {
            return response()->json(['error' => '관리자의 이메일 혹은 비밀번호가 올바르지 않습니다.'], 401);
        }
        $refreshToken = $this->tokenService->generateToken($credentials, 'refresh');

        return response()->json([
            'user' => auth()->user(),
            'access_token' => $token,
        ])->cookie('refresh_token', $refreshToken);
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
     *             @OA\Property (property="salon", type="boolean", description="미용실 권한", example=true),
     *             @OA\Property (property="admin", type="boolean", description="행정 권한", example=true),
     *             @OA\Property (property="restaurant", type="boolean", description="식당 권한", example=true),
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
            $this->authorize('master');
        } catch (AuthorizationException) {
            return $this->denied('마스터 관리자 권한이 없습니다.');
        }

        try {
            $validated = $request->validate([
                'admin_id'     => 'required|numeric',
                'privileges'   => 'required|array',
                'privileges.*' => 'required|exists:privileges,id',
            ]);
        } catch(ValidationException $validateException) {
            $errorStatus = $validateException->status;
            $errorMessage = $validateException->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        try {
            $admin = User::findOrFail($validated['admin_id']);
        } catch(ModelNotFoundException) {
            return response()->json(['error' => $this->modelExceptionMessage], 404);
        }

        unset($validated['admin_id']);

        $admin->privileges()->detach();

        $admin->privileges()->attach($validated['privileges']);

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
            $this->authorize('master');
        } catch (AuthorizationException) {
            return $this->denied('마스터 관리자 권한이 없습니다.');
        }

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
            $admin = User::findOrFail($validated['admin_id']);
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
    public function update(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('admin');
        } catch (AuthorizationException) {
            return $this->denied('관리자 권한이 없습니다.');
        }

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

        $adminId = auth()->id();

        try {
            $admin = User::findOrFail($adminId);
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
            $this->authorize('admin');
        } catch (AuthorizationException) {
            return $this->denied('관리자 권한이 없습니다.');
        }

        try {
            $validated = $request->validate([
                'type' => ['string', Rule::in($typeRule)],
            ]);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        $admins = User::where('admin', true)->get();

        if(isset($validated['type'])) {
            if($validated['type'] == 'unapproved') {
                $admins = $admins->where('approved', false);
            } else if($validated['type'] == 'approved') {
                $admins = $admins->where('approved', true);
            }
        }

        return response()->json(['admins' => $admins->values()]);
    }
}
