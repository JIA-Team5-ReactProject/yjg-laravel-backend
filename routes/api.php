<?php

use App\Http\Controllers\AbsenceController;
use App\Http\Controllers\Admin\AdminAfterServiceController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminSalonReservationController;
use App\Http\Controllers\Admin\NoticeController;
use App\Http\Controllers\Admin\SalonBreakTimeController;
use App\Http\Controllers\Admin\SalonBusinessHourController;
use App\Http\Controllers\Admin\SalonCategoryController;
use App\Http\Controllers\Admin\SalonServiceController;
use App\Http\Controllers\BusScheduleController;
use App\Http\Controllers\MeetingRoomController;
use App\Http\Controllers\MeetingRoomReservationController;
use App\Http\Controllers\QRController;
use App\Http\Controllers\RestaurantMenusController;
use App\Http\Controllers\RestaurantSemesterController;
use App\Http\Controllers\RestaurantWeekendController;
use App\Http\Controllers\SemesterMealTypeController;
use App\Http\Controllers\User\UserAfterServiceController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\UserSalonReservationController;
use App\Http\Controllers\WeekendMealTypeController;
use App\Models\AfterServiceComment;
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
    Route::post('/login', [UserController::class, 'login'])->middleware('user.approve')->name('user.login');
    Route::post('/google-login', [UserController::class, 'googleRegisterOrLogin'])->middleware('user.google.approve')->name('user.google.login');
});
Route::prefix('admin')->group(function () {
    Route::post('/',[AdminController::class, 'register'])->name('admin.register');
    Route::post('/login', [AdminController::class, 'login'])->middleware('admin.approve')->name('admin.login');
    Route::post('/find-email', [AdminController::class, 'findEmail'])->name('admin.find.email');
    Route::get('/verify-email/{email}', [AdminController::class, 'verifyUniqueAdminEmail'])->name('admin.verify.email');
//    Route::post('/forgot-password', [AdminController::class, 'forgotPassword'])->middleware('guest')->name('admin.forgot.password');
});

// 유저 및 공용
Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('user')->group(function () {
        Route::get('/qr', [QRController::class, 'generator']);
        Route::delete('/',[UserController::class, 'unregister'])->name('user.unregister');
        Route::post('/logout', [UserController::class, 'logout'])  ->name('user.logout');
        Route::get('/list', [UserController::class, 'userList'])->name('user.list');
        Route::patch('/' , [UserController::class, 'update'])->name('user.update');
        Route::patch('/approve', [UserController::class, 'approveRegistration'])->name('user.approve');
    });

    Route::prefix('salon')->group(function () {
        Route::prefix('reservation')->group(function () {
            Route::get('/', [UserSalonReservationController::class, 'index'])->name('user.salon.reservation.index');
            Route::get('/', [AdminSalonReservationController::class, 'show'])->name('salon.reservation.show');
            Route::post('/', [UserSalonReservationController::class, 'store'])->name('user.salon.reservation.store');
            Route::delete('/', [UserSalonReservationController::class, 'destroy'])->name('user.salon.reservation.destroy');
        });
        Route::prefix('hour')->group(function () {
            Route::get('/', [SalonBusinessHourController::class, 'index'])->name('admin.salon.hour.index');
            Route::get('/{day}', [SalonBusinessHourController::class, 'show'])->name('admin.salon.hour.show');
        });
        Route::get('/category', [SalonCategoryController::class, 'index'])->name('salon.category.index');
        Route::get('/service', [SalonServiceController::class, 'show'])->name('salon.service.show');
    });

    Route::prefix('after-service')->group(function () {
        Route::post('/', [UserAfterServiceController::class, 'store'])->name('user.as.store');
        Route::patch('/', [UserAfterServiceController::class, 'update'])->name('user.as.update');
        Route::delete('/{id}', [UserAfterServiceController::class, 'destroy'])->name('user.as.destroy');
        Route::get('/', [UserAfterServiceController::class, 'index'])->name('as.index');
        Route::get('/{id}', [UserAfterServiceController::class, 'show'])->name('as.show');
    });

    Route::prefix('meeting-room')->group(function () {
        Route::get('/check', [MeetingRoomController::class, 'checkReservation'])->name('meeting.check.reservation');
        Route::get('/', [MeetingRoomController::class, 'index'])->name('meeting.index');
        Route::prefix('reservation')->group(function () {
            Route::get('/{id}', [MeetingRoomReservationController::class, 'show'])->name('meeting.reservation.show');
            Route::post('/', [MeetingRoomReservationController::class, 'store']);
            Route::patch('/{id}', [MeetingRoomReservationController::class, 'reject'])->name('meeting.reservation.reject');
            Route::get('/user', [MeetingRoomReservationController::class, 'userIndex'])->name('meeting.reservation.index.user');
            Route::delete('/{id}', [MeetingRoomReservationController::class, 'destroy'])->name('meeting.reservation.destroy');
        });
    });

    Route::prefix('absence')->group(function () {
        Route::get('/', [AbsenceController::class, 'index'])->name('absence.index');
        Route::get('/user', [AbsenceController::class, 'userIndex'])->name('absence.user.index');
        Route::get('/{id}', [AbsenceController::class, 'show'])->name('absence.show');
        Route::post('/', [AbsenceController::class, 'store'])->name('absence.store');
        Route::patch('/{id}', [AbsenceController::class, 'update'])->name('absence.update');
        Route::delete('/{id}', [AbsenceController::class, 'destroy'])->name('absence.destroy');
    });

    Route::prefix('notice')->group(function () {
        Route::get('/', [NoticeController::class, 'index'])->name('admin.notice.index');
        Route::get('/{id}', [NoticeController::class, 'show'])->name('admin.notice.show');
    });
});


// 관리자
Route::middleware(['auth:sanctum', 'abilities:admin'])->group(function () {
    Route::prefix('admin')->group(function() {
        Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
        Route::post('/verify-password', [AdminController::class, 'verifyPassword'])->name('admin.verify.pw');
        Route::patch('/privilege', [AdminController::class, 'privilege'])->name('admin.privilege');
        Route::patch('/approve', [AdminController::class, 'approveRegistration'])->name('admin.approve');
        Route::patch('/', [AdminController::class, 'updateProfile'])->name('admin.update');
        Route::get('/list', [AdminController::class, 'adminList'])->name('admin.list');
        Route::delete('/',[AdminController::class, 'unregister'])->name('admin.unregister');
        Route::delete('/master/{id}', [AdminController::class, 'unregisterMaster'])->middleware('admin.master')->name('admin.master.unregister');
    });

    Route::prefix('salon')->group(function () {
        Route::prefix('break')->group(function () {
            Route::post('/', [SalonBreakTimeController::class, 'store'])->name('admin.salon.break.store');
            Route::delete('/', [SalonBreakTimeController::class, 'destroy'])->name('admin.salon.break.destroy');
        });
        Route::prefix('hour')->group(function () {
            Route::post('/', [SalonBusinessHourController::class, 'store'])->name('admin.salon.hour.store');
            Route::patch('/', [SalonBusinessHourController::class, 'update'])->name('admin.salon.hour.update');
            Route::delete('/{id}', [SalonBUsinessHourController::class, 'destroy'])->name('admin.salon.hour.destroy');
        });
        Route::prefix('category')->group(function() {
            Route::post('/', [SalonCategoryController::class, 'store'])->name('admin.salon.category.store');
            Route::patch('/', [SalonCategoryController::class, 'update'])->name('admin.salon.category.update');
            Route::delete('/{id}', [SalonCategoryController::class, 'destroy'])->name('admin.salon.category.destroy');
        });
        Route::prefix('service')->group(function () {
            Route::post('/', [SalonServiceController::class, 'store'])->name('admin.salon.service.store');
            Route::patch('/', [SalonServiceController::class, 'update'])->name('admin.salon.service.update');
            Route::delete('/{id}', [SalonServiceController::class, 'destroy'])->name('admin.salon.service.destroy');
        });
        Route::patch('/reservation', [AdminSalonReservationController::class, 'update'])->name('salon.reservation.status');
    });

    Route::prefix('notice')->group(function() {
        Route::post('/', [NoticeController::class, 'store'])->name('admin.notice.store');
        Route::patch('/{id}', [NoticeController::class, 'update'])->name('admin.notice.update');
        Route::delete('/{id}', [NoticeController::class, 'destroy'])->name('admin.notice.destroy');
    });

    Route::prefix('meeting-room')->group(function () {
        Route::get('/reservation', [MeetingRoomReservationController::class, 'index'])->name('meeting.reservation.index');
        Route::post('/', [MeetingRoomController::class, 'store'])->name('meeting.store');
        Route::delete('/{id}', [MeetingRoomController::class, 'destroy'])->name('meeting.destroy');
    });

    Route::prefix('after-service')->group(function () {
        Route::patch('/status/{id}', [AdminAfterServiceController::class, 'updateStatus'])->name('admin.as.status');
        Route::post('{id}/comment', [AfterServiceComment::class, 'store'])->name('as.comment.store');
        Route::patch('{id}/comment', [AfterServiceComment::class, 'update'])->name('as.comment.update');
        Route::delete('{id}/comment', [AfterServiceComment::class, 'destroy'])->name('as.comment.destroy');
    });
    Route::patch('/absence/reject/{id}', [AbsenceController::class, 'reject'])->name('absence.reject');
});


Route::prefix('restaurant')->group(function () {
    Route::post('/menu', [RestaurantMenusController::class, 'import']);
    Route::get('/semester/g/payment', [RestaurantSemesterController::class, 'getPayment']);
    Route::post('/semester/p/payment', [RestaurantSemesterController::class, 'setPayment']);
    Route::post('/semester', [RestaurantSemesterController::class, 'store']);
    Route::post('/weekend', [RestaurantWeekendController::class, 'store']);
    Route::post('/semester/meal-type', [SemesterMealTypeController::class, 'store']);
    Route::post('/weekend/meal-type', [WeekendMealTypeController::class, 'store']);
});

Route::prefix('bus')->group(function () {
    Route::prefix('schedule')->group(function () {
        Route::post('/', [BusScheduleController::class, 'store']);
        Route::delete('/{id}', [BusScheduleController::class, 'destroySchedule']);
    });
    Route::prefix('round')->group(function () {
        Route::post('/', [BusScheduleController::class, 'addRound']);
        Route::patch('/{id}', [BusScheduleController::class, 'update']);
        Route::get('/', [BusScheduleController::class, 'getRound']);
        Route::get('/schedule/{id}', [BusScheduleController::class, 'getRoundSchedule']);
        Route::delete('/{id}', [BusScheduleController::class, 'roundDestroy']);
    });
});


