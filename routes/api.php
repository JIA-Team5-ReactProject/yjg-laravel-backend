<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\QRController;
use App\Http\Controllers\RestaurantSemesterController;
use App\Http\Controllers\RestaurantWeekendController;

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
    Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');
});

Route::prefix('admin')->group(function() {
    Route::delete('/unregister/{id}',[AdminController::class, 'unregister'])->name('admin.unregister');
    Route::post('/login', [AdminController::class, 'login'])->name('admin.login');
    Route::post('/register',[AdminController::class, 'register'])->name('admin.register');
    Route::patch('/privilege', [AdminController::class, 'privilege'])->name('admin.privilege');
    Route::patch('/approve', [AdminController::class, 'approveRegistration'])->name('admin.approve');
    Route::patch('/update', [AdminController::class, 'updateProfile'])->name('admin.update');
    Route::get('/verify-email/{email}', [AdminController::class, 'verifyUniqueEmail'])->name('admin.verify.email');
    Route::post('/verify-password', [AdminController::class, 'verifyPassword'])->name('admin.verify.pw');
    Route::post('/find-email', [AdminController::class, 'findEmail'])->name('admin.find.email');
    Route::post('/reset-password', [AdminController::class, 'resetPassword'])->name('admin.reset.pw');
});


//
Route::get('/admin/qr', [QRController::class, 'generator']);
Route::get('/admin/qr-data', [QRController::class, 'getQrData']);

Route::post('/restaurant/semester', [RestaurantSemesterController::class, 'store']);
Route::post('/restaurant/weekend', [RestaurantWeekendController::class, 'store']);
