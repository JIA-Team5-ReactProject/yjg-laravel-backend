<?php

namespace App\Services;

use Illuminate\Http\Request;

class TokenService
{
    public function userToken($userData, string $tokenName, array $ability)
    {
        return $userData->createToken($tokenName, $ability)->plainTextToken;
    }

    public function adminToken($adminData, string $tokenName, array $ability)
    {
        return $adminData->createToken($tokenName, $ability)->plainTextToken;
    }

    public function revokeToken(Request $request)
    {
        return $request->user()->tokens()->delete();
    }
}
