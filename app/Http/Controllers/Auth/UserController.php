<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\DestroyException;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Services\TokenService;
use Google_Client;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function __construct(protected TokenService $tokenService) {
    }

    /**
     * @OA\Get (
     *     path="/api/user",
     *     tags={"학생"},
     *     summary="학생/관리자 정보",
     *     description="현재 인증된 학생/관리자의 정보를 반환",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function user(): JsonResponse
    {
        $user = auth()->user();

        return response()->json([$user['admin'] ? 'admin' : 'user' => $user]);
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
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string',
                'student_id' => 'required|string|unique:users,student_id',
                'phone_number' => 'required|string|unique:users,phone_number',
                'email' => 'required|string|unique:users',
                'password' => 'required|string',
            ]);
        } catch(ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['approved'] = true;

        $user = User::create($validated);

        if(!$user) return response()->json(['error' => '회원가입에 실패하였습니다.'],500);

        return response()->json(['user' => $user], 201);
    }

    /**
     * @OA\Delete (
     *     path="/api/unregister",
     *     tags={"학생"},
     *     summary="탈퇴",
     *     description="학생 및 관리자의 회원 탈퇴 시 사용합니다.",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     * @throws destroyexception
     */
    public function unregister(): JsonResponse
    {
        $userId = auth()->id();
        if (!User::destroy($userId)) {
            throw new destroyException('회원탈퇴에 실패하였습니다.');
        }

        return response()->json(['message' => '회원탈퇴 되었습니다.']);
    }

    /**
     * @OA\Post (
     *     path="/api/user/google-login",
     *     tags={"학생"},
     *     summary="로그인",
     *     description="유저 google 로그인 시 사용합니다.",
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
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function googleRegisterOrLogin(Request $request): JsonResponse
    {
        try {
            $credentials = $request->validate([
                'email'        => 'required|email',
                'displayName'  => 'required|string',
                'id_token'     => 'required|string',
                'os_type'      => 'required|string',
            ]);
        } catch(ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        $client = new Google_Client(['client_id' => $credentials['os_type'] == 'iOS' ?
            env('IOS_GOOGLE_CLIENT_ID') : env('AND_GOOGLE_CLIENT_ID')]);
        $payload = $client->verifyIdToken($credentials['id_token']);

        if (!$payload || $payload['email'] != $credentials['email'] || $payload['hd'] != 'g.yju.ac.kr') {
            return response()->json(['error' => '인증되지 않은 유저입니다.'], 401);
        }

        $user = User::updateOrCreate([
            'email' => $credentials['email'],
        ], [
            'name' => $credentials['displayName'],
        ]);

        if (!$user || !$token = $this->tokenService->generateToken($credentials, 'access')) {
            return response()->json(['error' => '토큰 생성에 실패하였습니다.'], 401);
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
     *     path="/api/user/login",
     *     tags={"학생"},
     *     summary="로그인",
     *     description="일반 로그인 시 사용합니다.",
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
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     * @throws ValidationException
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (! $token = $this->tokenService->generateToken($credentials, 'access')) {
            return response()->json(['error' => '토큰 생성에 실패하였습니다.'], 401);
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
     *     path="/api/logout",
     *     tags={"학생"},
     *     summary="로그아웃",
     *     description="유저 로그아웃",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Server Error"),
     * )
     */
    public function logout(): JsonResponse
    {
        auth()->logout();
        return response()->json(['message' => '로그아웃 되었습니다.']);
    }

    /**
     * @OA\Patch (
     *     path="/api/user",
     *     tags={"학생"},
     *     summary="개인정보 수정",
     *     description="유저의 개인정보 수정 시 사용합니다.",
     *     @OA\Requestbody(
     *         description="수정할 유저의 정보(미승인 유저의 경우에는 student_id 및 phone_number 만 필요합니다.)",
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
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="ModelNotFoundException"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="Server Error"),
     * )
     */
    public function update(Request $request): JsonResponse
    {
        $rulesForApproved = [
            'student_id'       => 'numeric|unique:users,student_id',
            'name'             => 'string',
            'phone_number'     => 'string|unique:users,phone_number',
            'current_password' => 'current_password',
            'new_password'     => 'string|required_with:current_password',
        ];

        $rulesForNotApproved = [
            'student_id'       => 'required|numeric|unique:users,student_id',
            'phone_number'     => 'required|string|unique:users,phone_number',
        ];

        $user = auth()->user();

        try {
            $validated = $request->validate($user['approved'] ? $rulesForApproved : $rulesForNotApproved);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        try {
            $user = User::findOrFail($user['id']);
        } catch(ModelNotFoundException) {
            return response()->json(['error' => '해당하는 유저가 존재하지 않습니다.'], 404);
        }

        if(!$user['approved']) {
            // 미승인 유저
            $user->student_id = $validated['student_id'];
            $user->phone_number = $validated['phone_number'];
        } else {
            // 승인 유저
            unset($validated['current_password']);

            foreach($validated as $key => $value) {
                if($key == 'new_password') {
                    $user->password = Hash::make($validated['new_password']);
                } else {
                    $user->$key = $value;
                }
            }
        }

        if(!$user->save()) return response()->json(['error' => '회원정보 수정에 실패하였습니다.'], 500);

        return response()->json(['message' => '회원정보가 수정되었습니다.']);
    }

    /**
     * @OA\Patch (
     *     path="/api/user/approve",
     *     tags={"학생"},
     *     summary="회원가입 승인",
     *     description="학생 회원가입 승인",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="ModelNotFoundException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function approveRegistration(): JsonResponse
    {
        $userId = auth()->id();

        try {
            $user = User::findOrFail($userId);
        } catch(ModelNotFoundException) {
            return response()->json(['error'=>$this->modelExceptionMessage], 404);
        }

        $user->approved = true;

        if(!$user->save()) {
            return response()->json(['error' => '유저를 승인하는 데 실패하였습니다.'], 500);
        }
        return response()->json(['message' => '유저가 승인되었습니다.']);
    }

    /**
     * @OA\Get (
     *     path="/api/user/verify-email/{id}",
     *     tags={"학생"},
     *     summary="이메일 중복 확인",
     *     description="학생 이메일 중복 여부를 확인할 때 사용합니다.",
     *      @OA\Parameter(
     *            name="id",
     *            description="중복을 확인할 학생의 이메일",
     *            required=true,
     *            in="path",
     *            @OA\Schema(type="string"),
     *        ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function verifyUniqueUserEmail(string $email): JsonResponse
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

    /**
     * @OA\Post (
     *     path="/api/find-email",
     *     tags={"학생"},
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
    public function findEmail(Request $request): JsonResponse
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
            $admin = User::where('phone_number', $validated['phone_number'])->where('name', $validated['name'])->firstOrFail();
        } catch (ModelNotFoundException) {
            return response()->json(['error' => $this->modelExceptionMessage], 404);
        }
        return response()->json(['admin' => $admin]);
    }

    /**
     * @OA\Post (
     *     path="/api/verify-password",
     *     tags={"학생"},
     *     summary="PW 체크",
     *     description="현재 로그인한 학생 및 관리자의 PW 체크",
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
    public function verifyPassword(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'password' => 'required|string|current_password',
            ]);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        return response()->json(['success' => '비밀번호가 일치합니다.']);
    }
}
