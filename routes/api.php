<?php

use App\Http\Controllers\Administrator\AbsenceController;
use App\Http\Controllers\Administrator\BusScheduleController;
use App\Http\Controllers\Administrator\NoticeController;
use App\Http\Controllers\AfterService\AfterServiceCommentController;
use App\Http\Controllers\AfterService\AfterServiceController;
use App\Http\Controllers\Auth\AdminController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\MeetingRoom\MeetingRoomController;
use App\Http\Controllers\MeetingRoom\MeetingRoomReservationController;
use App\Http\Controllers\PasswordResetCodeController;
use App\Http\Controllers\QRController;
use App\Http\Controllers\RefreshController;
use App\Http\Controllers\Restaurant\RestaurantMenusController;
use App\Http\Controllers\Restaurant\RestaurantSemesterController;
use App\Http\Controllers\Restaurant\RestaurantWeekendController;
use App\Http\Controllers\Restaurant\SemesterMealTypeController;
use App\Http\Controllers\Restaurant\WeekendMealTypeController;

use App\Http\Controllers\Restaurant\RestaurantApplyDivisionController;
use App\Http\Controllers\Restaurant\RestaurantAccountController;
use App\Http\Controllers\Salon\SalonBreakTimeController;
use App\Http\Controllers\Salon\SalonBusinessHourController;
use App\Http\Controllers\Salon\SalonCategoryController;
use App\Http\Controllers\Salon\SalonReservationController;
use App\Http\Controllers\Salon\SalonServiceController;
use App\Http\Middleware\LoginApproveCheck;
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
// Server Health Check
Route::get('/healthy', function () {
    return response()->json(['message' => 'HELLO WORLD ^_^']);
});

// 토큰 불필요
Route::get('/reset-password/verify', [PasswordResetCodeController::class, 'verifyPasswordResetCode'])->name('pw.reset.verify');
Route::get('/refresh', RefreshController::class)->middleware(['auth:users', 'token.type:refresh', 'approve:users']);
Route::prefix('user')->group(function () {
    Route::get('/verify-email/{id}', [UserController::class, 'verifyUniqueUserEmail'])->name('user.verify.email');
    Route::post('/', [UserController::class, 'register'])->name('user.register');
    Route::post('/login', [UserController::class, 'login'])->name('user.login');
    Route::post('/google-login', [UserController::class, 'googleRegisterOrLogin'])->name('user.google.login');
    Route::post('/reset-password', [UserController::class, 'resetPassword'])->name('user.reset.pw');
});
Route::prefix('admin')->group(function () {
    Route::post('/',[AdminController::class, 'register'])->name('admin.register');
    Route::middleware([LoginApproveCheck::class])->group(function () {
        Route::post('/login/web', [AdminController::class, 'webLogin'])->name('admin.login.web');
        Route::post('/login', [AdminController::class, 'login'])->name('admin.login');
    });
    Route::post('/find-email', [AdminController::class, 'findEmail'])->name('admin.find.email');
    Route::post('/reset-password', [AdminController::class, 'resetPassword'])->name('admin.reset.pw');
});

// 토큰 필요
Route::middleware(['auth:users', 'token.type:access', 'approve:users'])->group(function () {
    Route::prefix('admin')->group(function() {
        Route::get('/', [AdminController::class, 'admin'])->name('admin.info');
        Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
        Route::post('/verify-password', [AdminController::class, 'verifyPassword'])->name('admin.verify.pw');
        Route::patch('/privilege', [AdminController::class, 'privilege'])->name('admin.privilege');
        Route::patch('/approve', [AdminController::class, 'approveRegistration'])->name('admin.approve');
        Route::patch('/', [AdminController::class, 'updateProfile'])->name('admin.update');
        Route::get('/list', [AdminController::class, 'adminList'])->name('admin.list');
        Route::delete('/',[AdminController::class, 'unregister'])->name('admin.unregister');
        Route::delete('/master/{id}', [AdminController::class, 'unregisterMaster'])->name('admin.master.unregister');
    });

    Route::prefix('user')->group(function () {
        Route::get('/', [UserController::class, 'user'])->name('user.info');
        Route::get('/qr', [QRController::class, 'generator'])->name('qr');
        Route::delete('/',[UserController::class, 'unregister'])->name('user.unregister');
        Route::post('/logout', [UserController::class, 'logout'])  ->name('user.logout');
        Route::get('/list', [UserController::class, 'userList'])->name('user.list');
        Route::patch('/' , [UserController::class, 'update'])->name('user.update');
        Route::patch('/approve', [UserController::class, 'approveRegistration'])->name('user.approve');
    });

    Route::prefix('salon')->group(function () {
        Route::prefix('break')->group(function () {
            Route::post('/', [SalonBreakTimeController::class, 'store'])->name('salon.break.store');
            Route::delete('/', [SalonBreakTimeController::class, 'destroy'])->name('salon.break.destroy');
        });
        Route::prefix('hour')->group(function () {
            Route::post('/', [SalonBusinessHourController::class, 'store'])->name('salon.hour.store');
            Route::patch('/', [SalonBusinessHourController::class, 'update'])->name('salon.hour.update');
        });
        Route::prefix('category')->group(function() {
            Route::post('/', [SalonCategoryController::class, 'store'])->name('salon.category.store');
            Route::patch('/', [SalonCategoryController::class, 'update'])->name('salon.category.update');
            Route::delete('/{id}', [SalonCategoryController::class, 'destroy'])->name('salon.category.destroy');
        });
        Route::prefix('service')->group(function () {
            Route::post('/', [SalonServiceController::class, 'store'])->name('salon.service.store');
            Route::patch('/{id}', [SalonServiceController::class, 'update'])->name('salon.service.update');
            Route::delete('/{id}', [SalonServiceController::class, 'destroy'])->name('salon.service.destroy');
        });
        Route::patch('/reservation', [SalonReservationController::class, 'update'])->name('salon.reservation.status');
    });

    Route::prefix('notice')->group(function() {
        Route::post('/', [NoticeController::class, 'store'])->name('admin.notice.store');
        Route::patch('/{id}', [NoticeController::class, 'update'])->name('admin.notice.update');
        Route::delete('/{id}', [NoticeController::class, 'destroy'])->name('admin.notice.destroy');
    });

    Route::prefix('meeting-room')->group(function () {
        Route::patch('/{id}', [MeetingRoomController::class, 'update'])->name('meeting.update');
        Route::patch('/reservation/reject/{id}', [MeetingRoomReservationController::class, 'reject'])->name('meeting.reservation.reject');
        Route::post('/', [MeetingRoomController::class, 'store'])->name('meeting.store');
        Route::delete('/{id}', [MeetingRoomController::class, 'destroy'])->name('meeting.destroy');
    });

    Route::prefix('after-service')->group(function () {
        Route::patch('/status/{id}', [AfterServiceController::class, 'updateStatus'])->name('as.status');
        Route::post('{id}/comment', [AfterServiceCommentController::class, 'store'])->name('as.comment.store');
        Route::patch('/comment/{id}', [AfterServiceCommentController::class, 'update'])->name('as.comment.update');
        Route::delete('/comment/{id}', [AfterServiceCommentController::class, 'destroy'])->name('as.comment.destroy');
    });

    Route::prefix('absence')->group(function () {
        Route::get('/count', [AbsenceController::class, 'absenceCount'])->name('absence.count');
        Route::patch('/reject/{id}', [AbsenceController::class, 'reject'])->name('absence.reject');
    });

    // 공용
    Route::prefix('salon')->group(function () {
        Route::prefix('reservation')->group(function () {
            Route::get('/user', [SalonReservationController::class, 'index'])->name('salon.reservation.index.user');
            Route::get('/', [SalonReservationController::class, 'show'])->name('salon.reservation.show');
            Route::post('/', [SalonReservationController::class, 'store'])->name('salon.reservation.store');
            Route::delete('/{id}', [SalonReservationController::class, 'destroy'])->name('salon.reservation.destroy');
        });
        Route::get('/hour', [SalonBusinessHourController::class, 'index'])->name('salon.hour.index');
        Route::get('/hour/{day}', [SalonBusinessHourController::class, 'show'])->name('salon.hour.show');
        Route::get('/category', [SalonCategoryController::class, 'index'])->name('salon.category.index');
        Route::get('/service', [SalonServiceController::class, 'show'])->name('salon.service.show');
    });

    Route::prefix('after-service')->group(function () {
        Route::post('/', [AfterServiceController::class, 'store'])->name('as.store');
        Route::patch('/{id}', [AfterServiceController::class, 'update'])->name('as.update');
        Route::delete('/{id}', [AfterServiceController::class, 'destroy'])->name('as.destroy');
        Route::get('/', [AfterServiceController::class, 'index'])->name('as.index');
        Route::get('/user', [AfterServiceController::class, 'userIndex'])->name('as.index.user');
        Route::get('/{id}', [AfterServiceController::class, 'show'])->name('as.show');
        Route::get('/{id}/comment', [AfterServiceCommentController::class, 'show'])->name('as.comment.show');
    });

    Route::prefix('meeting-room')->group(function () {
        Route::get('/check', [MeetingRoomController::class, 'checkReservation'])->name('meeting.check.reservation');
        Route::get('/', [MeetingRoomController::class, 'index'])->name('meeting.index');
        Route::prefix('reservation')->group(function () {
            Route::get('/user', [MeetingRoomReservationController::class, 'userIndex'])->name('meeting.reservation.index.user');
            Route::get('/{id}', [MeetingRoomReservationController::class, 'show'])->name('meeting.reservation.show');
            Route::post('/', [MeetingRoomReservationController::class, 'store']);
            Route::get('/', [MeetingRoomReservationController::class, 'index'])->name('meeting.reservation.index');
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
        Route::get('/', [NoticeController::class, 'index'])->name('notice.index');
        Route::get('/recent', [NoticeController::class, 'recentIndex'])->name('notice.recent');
        Route::get('/recent/urgent', [NoticeController::class, 'recentUrgent'])->name('notice.recent.urgent');
        Route::get('/{id}', [NoticeController::class, 'show'])->name('notice.show');
    });

    Route::post('/restaurant/semester', [RestaurantSemesterController::class, 'store']);
    Route::post('/restaurant/weekend', [RestaurantWeekendController::class, 'store']);
    Route::get('/semester/g/payment', [RestaurantSemesterController::class, 'getPayment']);
    Route::post('/semester/meal-type', [SemesterMealTypeController::class, 'store']);
    Route::post('/weekend/meal-type', [WeekendMealTypeController::class, 'store']);
});

Route::prefix('restaurant')->group(function () {
    Route::post('/menu', [RestaurantMenusController::class, 'import']);
    Route::post('/menu/date', [RestaurantMenusController::class, 'store']);
    Route::get('/menu/get/year', [RestaurantMenusController::class, 'getyears']);
    Route::get('/menu/get/w', [RestaurantMenusController::class, 'getWeekMenu']);
    Route::get('/menu/get/d', [RestaurantMenusController::class, 'getDayMenu']);
    Route::delete('/menu/d/{id}', [RestaurantMenusController::class, 'deleteMenu']);
    Route::delete('/menu/date/d', [RestaurantMenusController::class, 'deleteDate']);

    Route::post('/account', [RestaurantAccountController::class, 'store']);
    Route::patch('/account/set', [RestaurantAccountController::class, 'update']);
    Route::get('/account/show', [RestaurantAccountController::class, 'show']);

    Route::post('/semester/p/payment/{id}', [RestaurantSemesterController::class, 'setPayment']);
    Route::get('/weekend/g/payment/{id}', [RestaurantWeekendController::class, 'getPayment']);
    Route::post('/weekend/p/payment/{id}', [RestaurantWeekendController::class, 'setPayment']);
    Route::get('/semester/apply', [RestaurantSemesterController::class, 'getRestaurantApply']);
    Route::get('/weekend/apply', [RestaurantWeekendController::class, 'getRestaurantApply']);


    Route::get('/semester/meal-type/get', [SemesterMealTypeController::class, 'getMealType']);
    Route::get('/weekend/meal-type/get', [WeekendMealTypeController::class, 'getMealType']);

    Route::delete('/semester/m/delete/{id}', [SemesterMealTypeController::class, 'delete']);
    Route::delete('/weekend/m/delete/{id}', [WeekendMealTypeController::class, 'delete']);
    Route::delete('/semester/delete/{id}', [RestaurantSemesterController::class, 'delete']);
    Route::delete('/weekend/delete/{id}', [RestaurantWeekendController::class, 'delete']);

    Route::get('/semester/show', [RestaurantSemesterController::class, 'show']);
    Route::get('/weekend/show', [RestaurantWeekendController::class, 'show']);
    Route::get('/semester/show/user', [RestaurantSemesterController::class, 'showUser']);
    Route::get('/semester/show/user/after', [RestaurantSemesterController::class, 'showUserAfter']);
    Route::get('/weekend/show/user/table', [RestaurantWeekendController::class, 'showUserTable']);

    Route::get('/weekend/show/sumApp', [RestaurantWeekendController::class, 'sumApplyApp']);
    Route::get('/weekend/show/sumWeb', [RestaurantWeekendController::class, 'sumApplyWeb']);

    Route::post('/apply/weekend/auto', [RestaurantApplyDivisionController::class, 'onWeekendAuto']);
    Route::patch('/apply/weekend/set', [RestaurantApplyDivisionController::class, 'setWeekendAuto']);
    Route::get('/apply/weekend/get', [RestaurantApplyDivisionController::class, 'getWeekendAuto']);
    Route::get('/apply/weekend/get/app', [RestaurantApplyDivisionController::class, 'getWeekendAutoApp']);

    Route::post('/apply/semester/auto', [RestaurantApplyDivisionController::class, 'onSemesterAuto']);
    Route::patch('/apply/semester/set', [RestaurantApplyDivisionController::class, 'setSemesterAuto']);
    Route::get('/apply/semester/get', [RestaurantApplyDivisionController::class, 'getSemesterAuto']);
    Route::get('/apply/semester/get/app', [RestaurantApplyDivisionController::class, 'getSemesterAutoApp']);

    Route::post('/apply/manual/set', [RestaurantApplyDivisionController::class, 'setManual']);
    Route::patch('/apply/manual', [RestaurantApplyDivisionController::class, 'manual']);
    Route::get('/apply/manual/get', [RestaurantApplyDivisionController::class, 'getManual']);
    Route::get('/apply/manual/get/app', [RestaurantApplyDivisionController::class, 'getManualApp']);

    Route::get('/apply/state', [RestaurantApplyDivisionController::class, 'showApplyState']);
    Route::post('/apply/state/on', [RestaurantApplyDivisionController::class, 'onApplyState']);

});

Route::prefix('bus')->group(function () {
    Route::prefix('schedule')->group(function () {
        Route::post('/', [BusScheduleController::class, 'store']);
        Route::delete('/{id}', [BusScheduleController::class, 'destroySchedule']);
        Route::patch('/update/{id}', [BusScheduleController::class, 'scheduleUpdate']);

    });
    Route::prefix('round')->group(function () {
        Route::post('/', [BusScheduleController::class, 'addRound']);
        Route::patch('/{id}', [BusScheduleController::class, 'update']);
        Route::get('/', [BusScheduleController::class, 'getRound']);
        Route::get('/schedule/{id}', [BusScheduleController::class, 'getRoundSchedule']);
        Route::delete('/{id}', [BusScheduleController::class, 'roundDestroy']);
        Route::get('/appSchedule', [BusScheduleController::class, 'getRoundAndSchedule']);
    });
});


