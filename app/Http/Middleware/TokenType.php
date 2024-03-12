<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $type = auth()->payload()->get('typ');
        if($type == 'refresh') {
            return $next($request);
        }
        return response()->json(['error' => 'refresh token이 필요합니다.'], 400);
    }
}
