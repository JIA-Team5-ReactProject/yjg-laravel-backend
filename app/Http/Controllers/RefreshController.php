<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\User;
use App\Services\TokenService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class RefreshController extends Controller
{
    public function __construct(protected TokenService $tokenService)
    {
    }

    /**
     * @OA\GET (
     *     path="/api/refresh",
     *     tags={"토큰"},
     *     summary="새 액세스 토큰 발급",
     *     description="헤더에 리프레쉬 토큰을 포함시켜 요청을 보내면, 해당 토큰을 통해 새 액세스 토큰을 반환",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="ModelNotFoundException"),
     *     @OA\Response(response="500", description="Server Error"),
     * )
     */
    public function __invoke(Request $request)
    {
        $guard = $request->grd;
        $model = null;

        if($guard == 'users') {
            try {
                $model = User::findOrFail(auth('users')->id());
            } catch (ModelNotFoundException $modelException) {
                return response()->json(['error' => '해당하는 유저가 없습니다.'], 404);
            }
        } else if ($guard == 'admins') {
            try {
                $model = Admin::findOrFail(auth('admins')->id());
            } catch (ModelNotFoundException $modelException) {
                return response()->json(['error' => '해당하는 관리자가 없습니다.'], 404);
            }
        }


        return response()->json(['refresh_token' => $this->tokenService->createAccessTokenByModel($guard, $model)]);
    }
}
