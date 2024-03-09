<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;

class TokenService
{
    public function createAccessToken(string $guard, array $credentials)
    {
        return auth($guard)->claims(['typ' => 'access'])->attempt($credentials);
    }

    public function createAccessTokenByModel(string $guard, User $user)
    {
        return auth($guard)->claims(['typ' => 'access'])->login($user);
    }

    public function createRefreshToken(string $guard, array $credentials)
    {
        return auth($guard)->claims(['typ' => 'refresh'])->setTTL(1440 * 7)->attempt($credentials);
    }
}
