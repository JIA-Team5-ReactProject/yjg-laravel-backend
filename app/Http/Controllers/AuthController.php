<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function __invoke()
    {
        return Socialite::driver('google')->with(['hd' => 'g.yju.ac.kr'])->redirect();
    }
}
