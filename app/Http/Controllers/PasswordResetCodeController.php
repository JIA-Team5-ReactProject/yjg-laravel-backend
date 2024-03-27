<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetCode;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PasswordResetCodeController extends Controller
{
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
                'email' => 'required|email|exists:admins,email',
                'code'  => 'required|string',
            ]);
        } catch (ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        // DB에서 조회
        try {
            PasswordResetCode::where('email', $validated['email'])
                ->where('code', $validated['code'])->firstOrFail();
        } catch (ModelNotFoundException) {
            return response()->json(['error' => '인증코드가 일치하지 않습니다.'], 401);
        }

        return response()->json(['message' => '코드가 인증되었습니다.']);
    }
}
