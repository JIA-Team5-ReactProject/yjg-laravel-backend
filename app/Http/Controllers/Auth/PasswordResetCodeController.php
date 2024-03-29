<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetCode;
use App\Models\User;
use App\Services\TokenService;
use DateTime;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PasswordResetCodeController extends Controller
{
    public function __construct(protected TokenService $tokenService)
    {
    }

    /**
     * @OA\Get (
     *     path="/api/reset-password/verify",
     *     tags={"비밀번호"},
     *     summary="비밀번호 초기화 코드 검증",
     *     description="메일로 받은 코드를 인증",
     *     @OA\Parameter(
     *           name="code",
     *           description="이메일로부터 받은 코드",
     *           required=true,
     *           in="query",
     *           @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter(
     *          name="email",
     *          description="사용자의 이메일",
     *          required=true,
     *          in="query",
     *          @OA\Schema(type="string"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="ModelNotFoundException"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function verifyPasswordResetCode(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'code'  => 'required|string',
            ]);
        } catch (ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        // DB에서 코드를 조회하여 검증
        try {
            $secret = PasswordResetCode::where('email', $validated['email'])
                ->where('code', $validated['code'])->firstOrFail();
        } catch (ModelNotFoundException) {
            return response()->json(['error' => '인증코드가 일치하지 않습니다.'], 401);
        }

        // 현재 시간
        $dateTime = new DateTime();
        $currentTime = $dateTime->format('Y-m-d H:i:s');

        // 만료 여부 확인
        if($secret->expires_at < $currentTime) {
            return response()->json(['error' => '만료된 인증코드입니다.'], 401);
        }

        // 이메일을 토대로 모델을 검색하고, 모델을 통해 토큰 생성
        try {
            $user = User::where('email', $validated['email'])->firstOrFail();
        } catch (ModelNotFoundException) {
            return response()->json(['error' => '이메일과 일치하는 유저를 찾을 수 없습니다.'], 404);
        }

        // email 타입 5분짜리 토큰
        $emailToken = $this->tokenService->generateTokenByModel($user, 'email');

        return response()->json(['message' => '코드가 인증되었습니다.', 'email_token' => $emailToken]);
    }
}
