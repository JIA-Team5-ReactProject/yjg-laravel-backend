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

    Route::post('/admin/logout', [AdminController::class, 'logout']);
});
Route::delete('/admin/unregister/{id}',[AdminController::class, 'unregister'])->name('admin.unregister');

Route::post('/admin/login', [AdminController::class, 'login']);

Route::post('/admin/register',[AdminController::class, 'register'])->name('admin.register');

Route::patch('/admin/privilege', [AdminController::class, 'privilege']);

Route::patch('/admin/approve', [AdminController::class, 'approveRegistration']);

Route::patch('/admin/update', [AdminController::class, 'updateProfile']);

Route::get('/admin/verify-email/{email}', [AdminController::class, 'verifyUniqueEmail']);

Route::get('/admin/verify-password', [AdminController::class, 'verifyPassword']);


