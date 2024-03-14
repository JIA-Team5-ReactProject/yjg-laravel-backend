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

    protected string $modelExceptionMessage = '아이디에 해당하는 데이터를 찾을 수 없습니다.';

    protected array $dayList = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];
    protected array $userValidateRules = [
        'name' => 'required|string',
        'student_id' => 'required|string',
        'phone_number' => 'required|string',
        'email' => 'required|string|unique:users',
        'password' => 'required|string',
    ];

    protected array $adminValidateRules = [
        'name' => 'required|string',
        'phone_number' => 'required|string',
        'email' => 'required|string|unique:admins',
        'password' => 'required|string',
    ];

    protected function denied(string $message = '권한이 없습니다.'): \Illuminate\Http\JsonResponse
    {
        return response()->json(['error' => $message], 403);
    }
}
