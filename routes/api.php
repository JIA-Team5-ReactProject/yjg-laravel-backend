<?php

use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/admin/unregister/{id}',[AdminController::class, 'unregister']);
    Route::post('/admin/logout', [AdminController::class, 'logout']);
});

Route::post('/admin/login', [AdminController::class, 'login']);

Route::post('/admin/register',[AdminController::class, 'register']);

Route::post('/admin/privilege', [AdminController::class, 'privilege']);



