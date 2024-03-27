<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class TokenService
{
    public function createAccessToken(array $credentials)
    {
        return auth()->claims(['typ' => 'access'])->attempt($credentials);
    }

    public function createAccessTokenByModel(Model $user)
    {
        return auth()->claims(['typ' => 'access'])->login($user);
    }

    public function createRefreshToken(array $credentials)
    {
        return auth()->claims(['typ' => 'refresh'])->setTTL(1440 * 7)->attempt($credentials);
    }


}
