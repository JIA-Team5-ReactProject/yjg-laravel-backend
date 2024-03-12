<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\DestroyException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TokenService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function __construct(protected TokenService $tokenService) {
    }
    /**
     * @OA\Post (
     *     path="/api/user",
     *     tags={"학생"},
     *     summary="회원가입(인증X)",
     *     description="회원가입",
     *     @OA\RequestBody(
     *          description="회원가입 정보",
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema (
     *                   @OA\Property (property="name", type="string", description="사용자 이름", example="관리자"),
     *                   @OA\Property (property="student_id", type="string", description="학번", example="1901234"),
     *                   @OA\Property (property="phone_number", type="string", description="전화번호", example="01012345678"),
     *                   @OA\Property (property="email", type="string", description="이메일", example="admin@gmail.com"),
     *                   @OA\Property (property="password", type="string", description="비밀번호", example="admin123"),
     *              )
     *          )
     *      ),
     *     @OA\Response(response="200", description="success"),
     *     @OA\Response(response="500", description="fail"),
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
            'name'         => $validated['name'],
            'student_id'   => $validated['student_id'],
            'phone_number' => $validated['phone_number'],
            'email'        => $validated['email'],
            'password'     => Hash::make($validated['password']),
            'approved'     => true,
        ]);

        if(!$user) return response()->json(['error' => 'Failed to register'],500);

        return response()->json(['user' => $user], 201);
    }

    /**
     * @OA\Delete (
     *     path="/api/user",
     *     tags={"학생"},
     *     summary="탈퇴",
     *     description="일반 학생 및 유학생 탈퇴",
     *     @OA\Response(response="200", description="success"),
     *     @OA\Response(response="500", description="fail"),
     * )
     * @throws destroyexception
     */
    public function unregister(Request $request)
    {
        $userId = auth('users')->id();
        if (!User::destroy($userId)) {
            throw new destroyException('Failed to destroy user', 500);
        }

        return response()->json(['message' => 'Destroy user successfully']);
    }

    /**
     * @OA\Post (
     *     path="/api/user/google-login",
     *     tags={"학생"},
     *     summary="로그인",
     *     description="유저 google 로그인",
     *     @OA\Requestbody(
     *          description="회원가입 정보",
     *          required=true,
     *          @OA\Mediatype(
     *               mediaType="application/json",
     *               @OA\Schema(
     *                    @OA\Property (property="email", type="string", description="이메일", example="admin@gmail.com"),
     *                    @OA\Property (property="displayName", type="string", description="이름", example="엄준식"),
     *               )
     *          )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="Validation Exception"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function googleRegisterOrLogin(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'displayName'  => 'required|string',
            ]);
        } catch(ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }
        // TODO : 토큰 받게되면 그거 인증하도록 수정
        $user = User::updateOrCreate([
            'email' => $credentials['email'],
        ], [
            'name' => $credentials['displayName'],
        ]);

        if (! $token = $this->tokenService->createAccessToken('users', $credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $refreshToken = $this->tokenService->createRefreshToken('users', $credentials);

        return response()->json([
            'user' => auth('users')->user(),
            'access_token' => $token,
            'refresh_token' => $refreshToken,
        ]);

    }

    /**
     * @OA\Post (
     *     path="/api/user/login",
     *     tags={"학생"},
     *     summary="로그인",
     *     description="일반 로그인",
     *     @OA\Requestbody(
     *         description="회원가입 정보",
     *         required=true,
     *         @OA\Mediatype(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property (property="email", type="string", description="이메일", example="admin@gmail.com"),
     *                 @OA\Property (property="password", type="string", description="비밀번호", example="admin123"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="success"),
     *     @OA\Response(response="500", description="fail"),
     * )
     * @throws ValidationException
     */
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
        } catch(Validationexception $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        if (! $token = $this->tokenService->createAccessToken('users', $credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $refreshToken = $this->tokenService->createRefreshToken('users', $credentials);

        return response()->json([
            'user' => auth('users')->user(),
            'access_token' => $token,
            'refresh_token' => $refreshToken,
        ]);
    }

    /**
     * @OA\Get (
     *     path="/api/user/logout",
     *     tags={"학생"},
     *     summary="로그아웃",
     *     description="유저 google 로그아웃",
     *     @OA\Response(response="200", description="success"),
     *     @OA\Response(response="500", description="fail"),
     * )
     */
    public function logout(Request $request)
    {
        auth('users')->logout();
        return response()->json(['success' => '성공적으로 로그아웃 되었습니다.']);
    }

    /**
     * @OA\Patch (
     *     path="/api/user",
     *     tags={"학생"},
     *     summary="개인정보 수정",
     *     description="유저 개인정보 수정",
     *     @OA\Requestbody(
     *         description="수정할 유저의 정보",
     *         required=true,
     *         @OA\Mediatype(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                  @OA\Property (property="student_id", type="string", description="정보를 수정할 유저의 아이디", example=1),
     *                  @OA\Property (property="name", type="string", description="변경할 이름", example="hyun"),
     *                  @OA\Property (property="phone_number", type="string", description="변경할 휴대폰 번호", example="01012345678"),
     *                  @OA\Property (property="current_password", type="string", description="이전 비밀번호", example="asdf321"),
     *                  @OA\Property (property="new_password", type="string", description="변경할 비밀번호", example="asdf123"),
     *             )
     *         ),
     *     ),
     *     @OA\Response(response="200", description="success"),
     *     @OA\Response(response="500", description="fail"),
     * )
     */
    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'student_id'       => 'numeric',
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

        $userId = auth('users')->id();

        try {
            $user = User::findOrFail($userId);
        } catch(modelNotFoundException $modelException) {
            $errorMessage = $modelException->getMessage();
            return response()->json(['error' => $errorMessage], 404);
        }

        unset($validated['current_password']);

        foreach($validated as $key => $value) {
            if($key == 'new_password') {
                $user->password = Hash::make($validated['new_password']);
            } else {
                $user->$key = $value;
            }
        }

        if(!$user->save()) return response()->json(['error' => '회원정보 수정에 실패하였습니다.'], 500);

        return response()->json(['success' => '회원정보를 수정하였습니다.']);
    }

    /**
     * @OA\Patch (
     *     path="/api/user/approve",
     *     tags={"학생"},
     *     summary="회원가입 승인",
     *     description="학생 회원가입 승인 (거부 시 계정 정보 삭제 됨)",
     *     @OA\Requestbody(
     *         description="아이디 및 승인 여부",
     *         required=true,
     *         @OA\Mediatype(
     *             mediaType="application/json",
     *            @OA\Schema (
     *              @OA\Property (property="approve", type="boolean", description="유학생 회원가입 승인 여부", example=false),
     *            )
     *         ),
     *     ),
     *     @OA\Response(response="200", description="success"),
     *     @OA\Response(response="500", description="fail"),
     * )
     */
    public function approveRegistration(Request $request)
    {
        try {
            $validated = $request->validate([
                'approve'  => 'required',
            ]);
        } catch(ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getmessage();
            return response()->json(['error'=>$request], $errorStatus);
        }

        $userId = auth('users')->id();

        try {
            $user = User::findOrFail($userId);
        } catch(modelNotFoundException $modelException) {
            $errorStatus = $modelException->status;
            $errorMessage = $modelException->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        if($validated['approve']) {
            $user->approved = true;
        } else {
            $user->delete();
            return response()->json(['success' => 'User deleted successfully']);
        }

        if(!$user->save()) {
            return response()->json(['error' => 'Failed to update approve status'], 500);
        }
        return response()->json(['message' => 'Update approve status successfully']);
    }

    /**
     * @OA\Get (
     *     path="/api/user/list",
     *     tags={"학생"},
     *     summary="승인 혹은 미승인 학생 목록",
     *     description="파라미터 값에 맞는 학생을 users 배열에 반환",
     *     @OA\Requestbody(
     *     description="승인 학생 조회의 경우 true, 미승인 학생 조회의 경우 false, 전체 조회 시에는 body 없이 요청만",
     *     required=false,
     *         @OA\Mediatype(
     *         mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="type", type="boolean", description="승인 미승인 여부", example=true),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="success"),
     *     @OA\Response(response="500", description="server error"),
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
            $errorMessage = $validationException->getmessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        if(!isset($validated['type'])) $users = User::all();
        else $users = User::where('approved' , $validated['type'])->get();

        return response()->json(['users' => $users]);
    }

    /**
     * @OA\Get (
     *     path="/api/user/verify-email/{id}",
     *     tags={"학생"},
     *     summary="이메일 중복 확인(인증X)",
     *     description="학생 이메일 중복 확인",
     *      @OA\Parameter(
     *            name="id",
     *            description="중복을 확인할 학생의 이메일",
     *            required=true,
     *            in="path",
     *            @OA\Schema(type="string"),
     *        ),
     *     @OA\Response(response="200", description="success"),
     *     @OA\Response(response="422", description="validation error"),
     * )
     */
    public function verifyUniqueUserEmail(string $email)
    {
        $rules = [
            'email' => 'required|email|unique:users,email'
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

}
