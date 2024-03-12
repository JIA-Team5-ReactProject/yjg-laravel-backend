<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMasterCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $admin = Admin::findOrFail(auth()->id());
        } catch (ModelNotFoundException $modelException) {
            return response()->json(['error' => '해당하는 관리자가 없습니다.'], 404);
        }

        if(!$admin->master) {
            return response()->json(['error' => '총관리자가 아닙니다.'], 500);
        }
        return $next($request);
    }
}
