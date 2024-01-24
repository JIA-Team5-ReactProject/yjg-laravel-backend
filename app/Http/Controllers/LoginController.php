<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * @OA\Post (
     *     path="/api/login",
     *     tags={"유저"},
     *     summary="웹 로그인",
     *     description="웹에서 로그인할 때",
     *     @OA\RequestBody(
     *         description="로그인에 필요한 정보",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="email", type="string", description="이메일", example="umjinsik@gmail.com"),
     *                 @OA\Property (property="password", type="string", description="비밀번호", example="umjunsik123"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail")
     * )
     */
    public function web(Request $request)
    {
        try {
            $validated = $request->validate($this->rules);
        } catch(ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        if (Auth::attempt($validated)) {
            $request->session()->regenerate();
        }
        return response()->json(['error'=>'The provided credentials do not match our records.'], 401);
    }

    public function app(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $user->createToken($request->device_name)->plainTextToken;
    }
}
