<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\SalonBreakTimeController;
use App\Http\Controllers\Admin\SalonBusinessHourController;
use App\Http\Controllers\Admin\SalonCategoryController;
use App\Http\Controllers\Admin\AdminSalonReservationController;
use App\Http\Controllers\Admin\SalonServiceController;
use App\Http\Controllers\QRController;
use App\Http\Controllers\RestaurantMealTypeController;
use App\Http\Controllers\RestaurantMenusController;
use App\Http\Controllers\RestaurantSemesterController;
use App\Http\Controllers\RestaurantWeekendController;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\UserSalonReservationController;
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
    Route::post('/user/logout', [UserController::class, 'logout'])  ->name('user.logout');
});

Route::prefix('admin')->group(function() {
    Route::prefix('salon-break')->group(function () {
        Route::post('/', [SalonBreakTimeController::class, 'store'])->name('admin.salon.break.store');
        Route::delete('/', [SalonBreakTimeController::class, 'destroy'])->name('admin.salon.break.destroy');
    });
    Route::post('/',[AdminController::class, 'register'])->name('admin.register');
    Route::post('/login', [AdminController::class, 'login'])->name('admin.login');
    Route::post('/verify-password', [AdminController::class, 'verifyPassword'])->name('admin.verify.pw');
    Route::post('/find-email', [AdminController::class, 'findEmail'])->name('admin.find.email');
    Route::patch('/privilege', [AdminController::class, 'privilege'])->name('admin.privilege');
    Route::patch('/approve', [AdminController::class, 'approveRegistration'])->name('admin.approve');
    Route::patch('/', [AdminController::class, 'updateProfile'])->name('admin.update');
    Route::get('/verify-email/{email}', [AdminController::class, 'verifyUniqueAdminEmail'])->name('admin.verify.email');
    Route::get('/', [AdminController::class, 'adminList'])->name('admin.list');
    Route::delete('/{id}',[AdminController::class, 'unregister'])->name('admin.unregister');
//    Route::post('/forgot-password', [AdminController::class, 'forgotPassword'])->middleware('guest')->name('admin.forgot.password');
    Route::prefix('salon-category')->group(function() {
       Route::get('/', [SalonCategoryController::class, 'index'])->name('admin.salon.category.index');
       Route::post('/', [SalonCategoryController::class, 'store'])->name('admin.salon.category.store');
       Route::patch('/', [SalonCategoryController::class, 'update'])->name('admin.salon.category.update');
       Route::delete('/{id}', [SalonCategoryController::class, 'destroy'])->name('admin.salon.category.destroy');
    });
    Route::prefix('salon-service')->group(function () {
        Route::get('/{id}/{gender}', [SalonServiceController::class, 'show'])->name('admin.salon.service.show');
        Route::post('/', [SalonServiceController::class, 'store'])->name('admin.salon.service.store');
        Route::patch('/', [SalonServiceController::class, 'update'])->name('admin.salon.service.update');
        Route::delete('/{id}', [SalonServiceController::class, 'destroy'])->name('admin.salon.service.destroy');
    });
    Route::prefix('salon-reservation')->group(function () {
       Route::get('/', [AdminSalonReservationController::class, 'show'])->name('admin.salon.reservation.show');
       Route::patch('/', [AdminSalonReservationController::class, 'update'])->name('admin.salon.reservation.status');
    });
    Route::prefix('salon-hour')->group(function () {
        Route::get('/', [SalonBusinessHourController::class, 'index'])->name('admin.salon.hour.index');
        Route::get('/{day}', [SalonBusinessHourController::class, 'show'])->name('admin.salon.hour.show');
        Route::post('/', [SalonBusinessHourController::class, 'store'])->name('admin.salon.hour.store');
        Route::patch('/', [SalonBusinessHourController::class, 'update'])->name('admin.salon.hour.update');
        Route::delete('/{id}', [SalonBUsinessHourController::class, 'destroy'])->name('admin.salon.hour.destroy');
    });
});

Route::prefix('user')->group(function () {
    Route::get('/', [UserController::class, 'userList'])->name('user.list');
    Route::get('/verify-email/{id}', [UserController::class, 'verifyUniqueUserEmail'])->name('user.verify.email');
    Route::get('/login', AuthController::class);
    Route::get('/auth/callback', [UserController::class, 'googleRegisterOrLogin'])->name('user.login');
    Route::patch('/' , [UserController::class, 'update'])->name('user.update');
    Route::delete('/{id}',[UserController::class, 'unregister'])->name('user.unregister');
    Route::prefix('foreigner')->group(function () {
        Route::post('/', [UserController::class, 'foreignerRegister'])->name('foreigner.register');
        Route::post('/login', [UserController::class, 'foreignerLogin'])->name('foreigner.login');
        Route::patch('/approve', [UserController::class, 'approveRegistration'])->name('foreigner.approve');
    });
    Route::prefix('salon-reservation')->group(function () {
        Route::get('/', [UserSalonReservationController::class, 'index'])->name('user.salon.reservation.index');
        Route::post('/', [UserSalonReservationController::class, 'store'])->name('user.salon.reservation.store');
        Route::delete('/', [UserSalonReservationController::class, 'destroy'])->name('user.salon.reservation.destroy');
    });

});



//
Route::get('/admin/qr', [QRController::class, 'generator']);
Route::post('/upload/excel', [RestaurantMenusController::class, 'import']);
Route::post('/restaurant/semester', [RestaurantSemesterController::class, 'store']);
Route::post('/restaurant/weekend', [RestaurantWeekendController::class, 'store']);
Route::post('/restaurant/mealtype', [RestaurantMealTypeController::class, 'store']);
