<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
* @OA\Info(
*     title="Yeungjin-Global", version="0.1", description="YJG API Documentation",
* )
*/
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected array $dayList = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];

    protected function denied(string $message = '권한이 없습니다.'): \Illuminate\Http\JsonResponse
    {
        return response()->json(['error' => $message], 403);
    }
}
