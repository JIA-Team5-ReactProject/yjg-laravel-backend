<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginApproveCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $admin = User::where('email', $request->email)->firstOrFail();
        } catch (ModelNotFoundException) {
            return response()->json(['error' => __('messages.404')], 404);
        }

        if(!$admin->approved) {
            return response()->json(['error' => __('auth.approve')], 403);
        }

        return $next($request);
    }
}
