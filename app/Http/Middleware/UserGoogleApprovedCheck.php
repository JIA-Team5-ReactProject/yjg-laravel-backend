<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserGoogleApprovedCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $check = User::where('email', $request->email)->first();

        if($check && !$check->approved) return response()->json(['error' => '아직 승인되지 않은 유저입니다.'], 500);
        return $next($request);
    }
}
