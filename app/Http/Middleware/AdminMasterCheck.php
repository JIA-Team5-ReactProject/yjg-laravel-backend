<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use App\Models\Privilege;
use App\Models\User;
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
            $admin = User::findOrFail(auth()->id());
        } catch (ModelNotFoundException $modelException) {
            return response()->json(['error' => '해당하는 관리자가 없습니다.'], 404);
        }
        $master = Privilege::where('privilege', 'master')->first();

        return response()->json($admin->privileges()->where('id', $master->privilege)->exists());
        if($admin->privileges()->where('id', $master->id)->exists()) {
            return $next($request);
        }

        return response()->json(['error' => '총관리자가 아닙니다.'], 500);
    }
}
