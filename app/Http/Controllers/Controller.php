<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
* @OA\Info(
*     title="Example", version="0.1", description="Example API Documentation",
*     @OA\Contact(
*         email="example@test.com",
*         name="Example"
*     )
* )
*/
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
