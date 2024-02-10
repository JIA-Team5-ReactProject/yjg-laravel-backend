<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SalonCategoryController;
use App\Http\Controllers\SalonServiceController;
use App\Http\Controllers\UserController;
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
    Route::post('/user/logout', [UserController::class, 'logout'])->name('user.logout');
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
//    Route::post('/forgot-password', [AdminController::class, 'forgotPassword'])->middleware('guest')->name('admin.forgot.password');
    Route::get('/unapproved', [AdminController::class, 'unapprovedAdmins'])->name('admin.unapproved');
    Route::get('/approved', [AdminController::class, 'approvedAdmins'])->name('admin.approved');
    Route::prefix('salon-category')->group(function() {
       Route::post('/store', [SalonCategoryController::class, 'store'])->name('admin.salon.category.store');
       Route::patch('/update', [SalonCategoryController::class, 'update'])->name('admin.salon.category.update');
       Route::delete('/destroy', [SalonCategoryController::class, 'destroy'])->name('admin.salon.category.destroy');
    });
    Route::prefix('salon-service')->group(function () {
        Route::post('/store', [SalonServiceController::class, 'store'])->name('admin.salon.service.store');
        Route::patch('/update', [SalonServiceController::class, 'update'])->name('admin.salon.service.update');
        Route::delete('/destroy/{id}', [SalonServiceController::class, 'destroy'])->name('admin.salon.service.destroy');
    });
});

Route::prefix('user')->group(function () {
    Route::get('/login', AuthController::class);
    Route::get('/auth/callback', [UserController::class, 'googleRegisterOrLogin'])->name('user.login');
    Route::patch('/update' , [UserController::class, 'update'])->name('user.update');
    Route::delete('/unregister/{id}',[UserController::class, 'unregister'])->name('user.unregister');
    Route::prefix('foreigner')->group(function () {
        Route::post('/register', [UserController::class, 'foreignerRegister'])->name('foreigner.register');
        Route::post('/login', [UserController::class, 'foreignerLogin'])->name('foreigner.login');
        Route::patch('/approve', [UserController::class, 'approveRegistration'])->name('foreigner.approve');
        Route::get('/unapproved', [UserController::class, 'unapprovedForeigners'])->name('foreigner.unapproved');
        Route::get('/approved', [UserController::class, 'approvedForeigners'])->name('foreigner.approved');
    });
});



