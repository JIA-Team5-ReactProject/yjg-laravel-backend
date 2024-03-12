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
    public function handle(Request $request, Closure $next, string $tokenType): Response
    {
        $type = auth()->payload()->get('typ');
        if($type == $tokenType) {
            return $next($request);
        }
        return response()->json(['error' => $tokenType.' token이 필요합니다.'], 400);
    }
}
