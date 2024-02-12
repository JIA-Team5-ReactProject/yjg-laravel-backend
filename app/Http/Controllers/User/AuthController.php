<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function __invoke()
    {
        return Socialite::driver('google')->with(['hd' => 'g.yju.ac.kr'])->redirect();
    }
}
