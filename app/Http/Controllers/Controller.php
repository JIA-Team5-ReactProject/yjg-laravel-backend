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

    protected array $userValidateRules = [
        'name' => 'required|string',
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
}
