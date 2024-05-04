<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     */
    public function handle($request, $next, ...$guards): mixed
    {
        if ($this->auth->guard()->check() &&
            auth()->payload()->get('typ') == 'access') {
            return $next($request);
        }

        return response()->json(['error' => '인증되지 않은 유저입니다.'], 401);
    }
}
