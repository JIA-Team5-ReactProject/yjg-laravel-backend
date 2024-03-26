<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminApprovedCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $admin = Admin::where('email', $request->email)->firstOrFail();
        } catch (ModelNotFoundException) {
            return response()->json(['error' => '해당하는 관리자가 없습니다.'], 404);
        }

        if(!$admin->approved) {
            return response()->json(['error' => '승인되지 않은 관리자입니다.'], 403);
        }

        return $next($request);
    }
}
