<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\DestroyException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ResetPasswordService;
use App\Services\TokenService;
use Google_Client;
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
     * @OA\Get (
     *     path="/api/user",
     *     tags={"유저"},
     *     summary="유저 정보",
     *     description="현재 인증된 유저의 정보를 반환",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function user(): \Illuminate\Http\JsonResponse
    {
        return response()->json(['user' => auth('users')->user()]);
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
    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate($this->userValidateRules);
        } catch(ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['approved']   = true;

        $user = User::create($validated);

        if(!$user) return response()->json(['error' => '회원가입에 실패하였습니다.'],500);

        return response()->json(['user' => $user], 201);
    }

    /**
     * @OA\Delete (
     *     path="/api/user",
     *     tags={"학생"},
     *     summary="탈퇴",
     *     description="학생 유저의 회원 탈퇴 시 사용합니다.",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     * @throws destroyexception
     */
    public function unregister(): \Illuminate\Http\JsonResponse
    {
        $userId = auth('users')->id();
        if (!User::destroy($userId)) {
            throw new destroyException('회원탈퇴에 실패하였습니다.', 500);
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
    public function googleRegisterOrLogin(Request $request)
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


        if ($payload['email'] != $credentials['email'] || $payload['hd'] != 'g.yju.ac.kr') {
            return response()->json(['error' => '인증되지 않은 유저입니다.'], 401);
        }

        $user = User::updateOrCreate([
            'email' => $credentials['email'],
        ], [
            'name' => $credentials['displayName'],
        ]);

        if (!$user || !$token = $this->tokenService->createAccessToken('users', $credentials)) {
            return response()->json(['error' => '토큰 생성에 실패하였습니다.'], 401);
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
    public function login(Request $request): \Illuminate\Http\JsonResponse
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
            return response()->json(['error' => '토큰 생성에 실패하였습니다.'], 401);
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
     *     path="/api/user/logout",
     *     tags={"학생"},
     *     summary="로그아웃",
     *     description="유저 로그아웃",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Server Error"),
     * )
     */
    public function logout(): \Illuminate\Http\JsonResponse
    {
        auth('users')->logout();
        return response()->json(['message' => '로그아웃 되었습니다.']);
    }

    /**
     * @OA\Patch (
     *     path="/api/user",
     *     tags={"학생"},
     *     summary="개인정보 수정",
     *     description="유저의 개인정보 수정 시 사용합니다.",
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
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="ModelNotFoundException"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="Server Error"),
     * )
     */
    public function update(Request $request): \Illuminate\Http\JsonResponse
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
        } catch(modelNotFoundException) {
            return response()->json(['error' => '해당하는 유저가 존재하지 않습니다.'], 404);
        }

        //TODO: 대량할당으로 수정하기

        unset($validated['current_password']);

        foreach($validated as $key => $value) {
            if($key == 'new_password') {
                $user->password = Hash::make($validated['new_password']);
            } else {
                $user->$key = $value;
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
    public function approveRegistration(): \Illuminate\Http\JsonResponse
    {
        $userId = auth('users')->id();

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
     *     path="/api/user/list",
     *     tags={"학생"},
     *     summary="승인 혹은 미승인 학생 목록",
     *     description="type 값과 일치하는 학생을 users 배열에 반환",
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
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="ModelNotFoundException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function userList(Request $request): \Illuminate\Http\JsonResponse
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
    public function verifyUniqueUserEmail(string $email): \Illuminate\Http\JsonResponse
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
     *     path="/api/user/reset-password",
     *     tags={"학생"},
     *     summary="비밀번호 초기화",
     *     description="회원가입 시 입력한 이름, 이메일을 검증하고, 메일 전송 후 코드를 인증",
     *     @OA\RequestBody(
     *         description="name & email",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="name", type="string", description="이름", example="testname"),
     *                 @OA\Property (property="email", type="string", description="이메일", example="test@test.com"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="ModelNotFoundException"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function resetPassword(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|exists:users,email',
                'name'  => 'required|string',
            ]);
        } catch (ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        try {
            User::where('email', $validated['email'])->where('name', $validated['name'])->firstOrFail();
        } catch (ModelNotFoundException) {
            return response()->json(['error' => '해당하는 유저가 존재하지 않습니다.'], 404);
        }

        $resetPasswordService = new ResetPasswordService($validated['email']);

        return $resetPasswordService();
    }
}
