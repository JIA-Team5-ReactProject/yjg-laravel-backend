<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateRefresh
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if($request->cookie('refresh_token') !== null &&
            auth()->setToken($request->cookie('refresh_token'))->check() &&
            auth()->payload()->get('typ') == 'refresh') {
            return $next($request);
        }

        return response()->json(['error' => '인증되지 않은 유저입니다.(refresh)'], 401);
    }
}
