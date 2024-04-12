<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Privilege;
use Illuminate\Http\JsonResponse;

class PrivilegeController extends Controller
{
    /**
     * @OA\Get (
     *     path="/api/admin/privilege",
     *     tags={"관리자"},
     *     summary="전체 권한 목록",
     *     description="privileges 테이블에 저장된 모든 권한 목록을 반환합니다.",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Server Error"),
     * )
     */
    public function __invoke(): JsonResponse
    {
        $privileges = Privilege::where('privilege', '<>', 'master')->get();

        return response()->json(['privileges' => $privileges]);
    }
}
