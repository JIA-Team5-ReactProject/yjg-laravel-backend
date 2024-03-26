<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApproveCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $type = auth()->payload()->get('grd');

        $user = auth($type)->user();

        if(!$user->approved) {
            return response()->json(['error' => '승인되지 않은 유저입니다.'], 403);
        }

        return $next($request);
    }
}
