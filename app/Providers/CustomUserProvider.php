<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Hash;

class CustomUserProvider implements UserProvider
{
    public function retrieveByCredentials(array $credentials)
    {
        return User::where('email', $credentials['email'])
            ->first();
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        if(isset($credentials['displayName'])) {
            if(User::where('name', $credentials['displayName'])
                ->first()) {
                return true;
            }
        } else if(Hash::check($credentials['password'], $user->getAuthPassword())) {
            return true;
        }
        return false;
    }

    public function retrieveById($identifier)
    {
        return User::find($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        // TODO: Implement retrieveByToken() method.
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // TODO: Implement updateRememberToken() method.
    }
}
