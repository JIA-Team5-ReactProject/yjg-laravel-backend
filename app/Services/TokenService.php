<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class TokenService
{
    public function generateToken(array $credentials, string $type)
    {
        if($type == 'refresh') {
            return auth()->claims(['typ' => 'refresh'])->setTTL(1440 * 7)->attempt($credentials);
        }
        return auth()->claims(['typ' => $type])->attempt($credentials);
    }

    public function generateTokenByModel(Model $user, string $type)
    {
        return auth()->claims(['typ' => $type])->login($user);
    }
}
