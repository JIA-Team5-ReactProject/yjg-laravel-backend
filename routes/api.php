<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\NoticeController;
use App\Http\Controllers\Admin\SalonBreakTimeController;
use App\Http\Controllers\Admin\SalonBusinessHourController;
use App\Http\Controllers\Admin\SalonCategoryController;
use App\Http\Controllers\Admin\AdminSalonReservationController;
use App\Http\Controllers\Admin\SalonServiceController;


use App\Http\Controllers\BusScheduleController;
use App\Http\Controllers\QRController;

use App\Http\Controllers\RestaurantMenusController;
use App\Http\Controllers\RestaurantSemesterController;
use App\Http\Controllers\RestaurantWeekendController;

use App\Http\Controllers\SemesterMealTypeController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\UserSalonReservationController;
use App\Http\Controllers\WeekendMealTypeController;
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

// 토큰 불필요
Route::prefix('user')->group(function () {
    Route::get('/verify-email/{id}', [UserController::class, 'verifyUniqueUserEmail'])->name('user.verify.email');
    Route::post('/', [UserController::class, 'register'])->name('user.register');
    Route::post('/login', [UserController::class, 'login'])->middleware('approve')->name('user.login');
    Route::post('/google-login', [UserController::class, 'googleRegisterOrLogin'])->name('user.google.login');
});
Route::prefix('admin')->group(function () {
    Route::post('/',[AdminController::class, 'register'])->name('admin.register');
    Route::post('/login', [AdminController::class, 'login'])->name('admin.login');
    Route::post('/find-email', [AdminController::class, 'findEmail'])->name('admin.find.email');
    Route::get('/verify-email/{email}', [AdminController::class, 'verifyUniqueAdminEmail'])->name('admin.verify.email');
//    Route::post('/forgot-password', [AdminController::class, 'forgotPassword'])->middleware('guest')->name('admin.forgot.password');
});

// 토큰 필요
Route::middleware(['auth:sanctum', 'abilities:user'])->group(function () {
    Route::prefix('user')->group(function () {
        Route::delete('/',[UserController::class, 'unregister'])->name('user.unregister');
        Route::post('/logout', [UserController::class, 'logout'])  ->name('user.logout');
        Route::get('/list', [UserController::class, 'userList'])->name('user.list');
        Route::patch('/' , [UserController::class, 'update'])->name('user.update');
        Route::patch('/approve', [UserController::class, 'approveRegistration'])->name('user.approve');
        Route::prefix('salon-reservation')->group(function () {
            Route::get('/', [UserSalonReservationController::class, 'index'])->name('user.salon.reservation.index');
            Route::post('/', [UserSalonReservationController::class, 'store'])->name('user.salon.reservation.store');
            Route::delete('/', [UserSalonReservationController::class, 'destroy'])->name('user.salon.reservation.destroy');
        });
    });
});

Route::middleware(['auth:sanctum', 'abilities:admin'])->group(function () {
    Route::prefix('admin')->group(function() {
        Route::prefix('salon-break')->group(function () {
            Route::post('/', [SalonBreakTimeController::class, 'store'])->name('admin.salon.break.store');
            Route::delete('/', [SalonBreakTimeController::class, 'destroy'])->name('admin.salon.break.destroy');
        });
        Route::prefix('salon-category')->group(function() {
            Route::get('/', [SalonCategoryController::class, 'index'])->name('admin.salon.category.index');
            Route::post('/', [SalonCategoryController::class, 'store'])->name('admin.salon.category.store');
            Route::patch('/', [SalonCategoryController::class, 'update'])->name('admin.salon.category.update');
            Route::delete('/{id}', [SalonCategoryController::class, 'destroy'])->name('admin.salon.category.destroy');
        });
        Route::prefix('salon-service')->group(function () {
            Route::get('/', [SalonServiceController::class, 'show'])->name('admin.salon.service.show');
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
        Route::prefix('notice')->group(function() {
            Route::get('/', [NoticeController::class, 'index'])->name('admin.notice.index');
            Route::get('/{id}', [NoticeController::class, 'show'])->name('admin.notice.show');
            Route::post('/', [NoticeController::class, 'store'])->name('admin.notice.store');
            Route::patch('/{id}', [NoticeController::class, 'update'])->name('admin.notice.update');
            Route::delete('/{id}', [NoticeController::class, 'destroy'])->name('admin.notice.destroy');
        });
        Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
        Route::post('/verify-password', [AdminController::class, 'verifyPassword'])->name('admin.verify.pw');
        Route::patch('/privilege', [AdminController::class, 'privilege'])->name('admin.privilege');
        Route::patch('/approve', [AdminController::class, 'approveRegistration'])->name('admin.approve');
        Route::patch('/', [AdminController::class, 'updateProfile'])->name('admin.update');
        Route::get('/list', [AdminController::class, 'adminList'])->name('admin.list');
        Route::delete('/',[AdminController::class, 'unregister'])->name('admin.unregister');
    });
});




Route::get('/admin/qr', [QRController::class, 'generator']);
Route::post('/upload/excel', [RestaurantMenusController::class, 'import']);
Route::get('/restaurant/semester/g/payment', [RestaurantSemesterController::class, 'getPayment']);
Route::post('/restaurant/semester/p/payment', [RestaurantSemesterController::class, 'setPayment']);
Route::post('/restaurant/semester', [RestaurantSemesterController::class, 'store']);
Route::post('/semester/mealtype', [SemesterMealTypeController::class, 'store']);
Route::post('/restaurant/weekend', [RestaurantWeekendController::class, 'store']);
Route::post('/weekend/mealtype', [WeekendMealTypeController::class, 'store']);



Route::post('/bus/schedule', [BusScheduleController::class, 'store']);
Route::patch('/bus/schedule/update/{id}', [BusScheduleController::class, 'update']);
Route::delete('/bus/schedule/delete/{id}', [BusScheduleController::class, 'destroy']);
Route::post('/bus/addRound', [BusScheduleController::class, 'addRound']);
Route::get('/bus/getRound', [BusScheduleController::class, 'getRound']);
Route::get('/bus/getRoundSchedule/{id}', [BusScheduleController::class, 'getRoundSchedule']);

Route::delete('/bus/round/delete/{id}', [BusScheduleController::class, 'roundDestroy']);
