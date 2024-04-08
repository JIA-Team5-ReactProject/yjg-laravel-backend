<?php

use App\Http\Controllers\Administrator\AbsenceController;
use App\Http\Controllers\Administrator\BusScheduleController;
use App\Http\Controllers\Administrator\NoticeController;
use App\Http\Controllers\AfterService\AfterServiceCommentController;
use App\Http\Controllers\AfterService\AfterServiceController;
use App\Http\Controllers\Auth\AdminController;
use App\Http\Controllers\Auth\PasswordResetCodeController;
use App\Http\Controllers\Auth\PrivilegeController;
use App\Http\Controllers\Auth\RefreshController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\MeetingRoom\MeetingRoomController;
use App\Http\Controllers\MeetingRoom\MeetingRoomReservationController;
use App\Http\Controllers\QRController;
use App\Http\Controllers\Restaurant\RestaurantAccountController;
use App\Http\Controllers\Restaurant\RestaurantApplyDivisionController;
use App\Http\Controllers\Restaurant\RestaurantMenusController;
use App\Http\Controllers\Restaurant\RestaurantSemesterController;
use App\Http\Controllers\Restaurant\RestaurantWeekendController;
use App\Http\Controllers\Restaurant\SemesterMealTypeController;
use App\Http\Controllers\Restaurant\WeekendMealTypeController;
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

/** 비밀번호 초기화 관련 */
Route::get('/reset-password/verify', [PasswordResetCodeController::class, 'verifyPasswordResetCode'])->name('pw.reset.verify');
Route::post('/reset-password', [PasswordResetCodeController::class, 'sendPasswordResetCode'])->name('pw.reset.send');
Route::patch('/reset-password' , [PasswordResetCodeController::class, 'resetPassword'])
    ->name('pw.reset')->middleware(['auth:users', 'token.type:email']);// 이메일 타입의 토큰도 허용함

/** 메일 찾기 */
Route::post('/find-email', [UserController::class, 'findEmail'])->name('find.email');

/** 이메일 중복 검사 */
Route::get('/verify-email/{id}', [UserController::class, 'verifyUniqueUserEmail'])->name('verify.email'); //수정

/** 토큰 리프레시 */
Route::get('/refresh', RefreshController::class)->middleware(['auth:users', 'token.type:refresh', 'approve:users']);

/**
 * 학생 인증 관련
 */
Route::prefix('user')->group(function () {
    Route::post('/login', [UserController::class, 'login'])->name('user.login'); // 서비스
    Route::post('/google-login', [UserController::class, 'googleRegisterOrLogin'])->name('user.google.login');
    Route::post('/', [UserController::class, 'register'])->name('user.register'); // 서비스
});

/**
 * 관리자 인증 관련
 */
Route::prefix('admin')->group(function () {
    Route::post('/',[AdminController::class, 'register'])->name('admin.register'); // 서비스
    Route::middleware([LoginApproveCheck::class])->group(function () {
        Route::post('/login/web', [AdminController::class, 'webLogin'])->name('admin.login.web');
        Route::post('/login', [AdminController::class, 'login'])->name('admin.login'); // 서비스
    });
});

/** 토큰이 필요한 기능 */
Route::middleware(['auth:users', 'token.type:access', 'approve:users'])->group(function () {
    /** 학생 및 관리자 공용 */
    Route::delete('/unregister',[UserController::class, 'unregister'])->name('unregister'); // 수정
    Route::post('/logout', [UserController::class, 'logout'])  ->name('logout'); // 수정
    Route::post('/verify-password', [UserController::class, 'verifyPassword'])->name('verify.pw'); // 수정

    /** 관리자용 */
    Route::prefix('admin')->group(function() {
        Route::prefix('privilege')->group(function () {
            Route::patch('/', [AdminController::class, 'privilege'])->name('admin.privilege.update');
            Route::get('/', PrivilegeController::class)->name('admin.privilege.list');
        });
        Route::patch('/', [AdminController::class, 'update'])->name('admin.update');
        Route::get('/list', [AdminController::class, 'adminList'])->name('admin.list');
        Route::delete('/master/{id}', [AdminController::class, 'unregisterMaster'])->name('admin.master.unregister');
        Route::patch('/approve', [AdminController::class, 'approveRegistration'])->name('admin.approve'); // 서비스 2
    });

    /** 학생용 */
    Route::prefix('user')->group(function () {
        Route::patch('/', [UserController::class, 'update'])->name('user.update')->withoutMiddleware('approve:users');
        Route::get('/', [UserController::class, 'user'])->name('user.info');
        Route::get('/qr', [QRController::class, 'generator'])->name('qr');
        Route::patch('/approve', [UserController::class, 'approveRegistration'])->name('user.approve')->withoutMiddleware('approve:users'); // 서비스 2
    });

    /** 미용실 */
    Route::prefix('salon')->group(function () {
        Route::prefix('break')->group(function () {
            Route::post('/', [SalonBreakTimeController::class, 'store'])->name('salon.break.store');
            Route::delete('/', [SalonBreakTimeController::class, 'destroy'])->name('salon.break.destroy');
        });
        Route::prefix('hour')->group(function () {
            Route::post('/', [SalonBusinessHourController::class, 'store'])->name('salon.hour.store');
            Route::patch('/', [SalonBusinessHourController::class, 'update'])->name('salon.hour.update');
            Route::get('/', [SalonBusinessHourController::class, 'index'])->name('salon.hour.index');
            Route::get('/{day}', [SalonBusinessHourController::class, 'show'])->name('salon.hour.show');
        });
        Route::prefix('category')->group(function() {
            Route::post('/', [SalonCategoryController::class, 'store'])->name('salon.category.store');
            Route::patch('/', [SalonCategoryController::class, 'update'])->name('salon.category.update');
            Route::delete('/{id}', [SalonCategoryController::class, 'destroy'])->name('salon.category.destroy');
            Route::get('/', [SalonCategoryController::class, 'index'])->name('salon.category.index');
        });
        Route::prefix('service')->group(function () {
            Route::post('/', [SalonServiceController::class, 'store'])->name('salon.service.store');
            Route::patch('/{id}', [SalonServiceController::class, 'update'])->name('salon.service.update');
            Route::delete('/{id}', [SalonServiceController::class, 'destroy'])->name('salon.service.destroy');
            Route::get('/', [SalonServiceController::class, 'show'])->name('salon.service.show');
        });
        Route::prefix('reservation')->group(function () {
            Route::patch('/', [SalonReservationController::class, 'update'])->name('salon.reservation.status');
            Route::get('/user', [SalonReservationController::class, 'index'])->name('salon.reservation.index.user');
            Route::get('/', [SalonReservationController::class, 'show'])->name('salon.reservation.show');
            Route::post('/', [SalonReservationController::class, 'store'])->name('salon.reservation.store');
            Route::delete('/{id}', [SalonReservationController::class, 'destroy'])->name('salon.reservation.destroy');
        });
    });

    /** 공지사항 */
    Route::prefix('notice')->group(function() {
        Route::post('/', [NoticeController::class, 'store'])->name('admin.notice.store');
        Route::patch('/{id}', [NoticeController::class, 'update'])->name('admin.notice.update');
        Route::delete('/{id}', [NoticeController::class, 'destroy'])->name('admin.notice.destroy');
        Route::get('/', [NoticeController::class, 'index'])->name('notice.index');
        Route::prefix('recent')->group(function () {
            Route::get('/', [NoticeController::class, 'recentIndex'])->name('notice.recent');
            Route::get('/urgent', [NoticeController::class, 'recentUrgent'])->name('notice.recent.urgent');
        });
        Route::get('/{id}', [NoticeController::class, 'show'])->name('notice.show');
    });

    /** 회의실 */
    Route::prefix('meeting-room')->group(function () {
        Route::patch('/{id}', [MeetingRoomController::class, 'update'])->name('meeting.update');
        Route::post('/', [MeetingRoomController::class, 'store'])->name('meeting.store');
        Route::delete('/{id}', [MeetingRoomController::class, 'destroy'])->name('meeting.destroy');
        Route::get('/check', [MeetingRoomController::class, 'checkReservation'])->name('meeting.check.reservation');
        Route::get('/', [MeetingRoomController::class, 'index'])->name('meeting.index');
        Route::prefix('reservation')->group(function () {
            Route::patch('/reject/{id}', [MeetingRoomReservationController::class, 'reject'])->name('meeting.reservation.reject');
            Route::get('/user', [MeetingRoomReservationController::class, 'userIndex'])->name('meeting.reservation.index.user');
            Route::get('/{id}', [MeetingRoomReservationController::class, 'show'])->name('meeting.reservation.show');
            Route::post('/', [MeetingRoomReservationController::class, 'store']);
            Route::get('/', [MeetingRoomReservationController::class, 'index'])->name('meeting.reservation.index');
            Route::delete('/{id}', [MeetingRoomReservationController::class, 'destroy'])->name('meeting.reservation.destroy');
        });
    });

    /** AS */
    Route::prefix('after-service')->group(function () {
        Route::prefix('comment')->group(function () {
            Route::patch('/comment/{id}', [AfterServiceCommentController::class, 'update'])->name('as.comment.update');
            Route::delete('/comment/{id}', [AfterServiceCommentController::class, 'destroy'])->name('as.comment.destroy');
        });
        Route::patch('/status/{id}', [AfterServiceController::class, 'updateStatus'])->name('as.status');
        Route::post('{id}/comment', [AfterServiceCommentController::class, 'store'])->name('as.comment.store');
        Route::post('/', [AfterServiceController::class, 'store'])->name('as.store');
        Route::patch('/{id}', [AfterServiceController::class, 'update'])->name('as.update');
        Route::delete('/{id}', [AfterServiceController::class, 'destroy'])->name('as.destroy');
        Route::get('/', [AfterServiceController::class, 'index'])->name('as.index');
        Route::get('/user', [AfterServiceController::class, 'userIndex'])->name('as.index.user');
        Route::get('/{id}', [AfterServiceController::class, 'show'])->name('as.show');
        Route::get('/{id}/comment', [AfterServiceCommentController::class, 'show'])->name('as.comment.show');
    });

    /** 외박 및 외출 */
    Route::prefix('absence')->group(function () {
        Route::get('/count', [AbsenceController::class, 'absenceCount'])->name('absence.count');
        Route::patch('/reject/{id}', [AbsenceController::class, 'reject'])->name('absence.reject');
        Route::get('/', [AbsenceController::class, 'index'])->name('absence.index');
        Route::get('/user', [AbsenceController::class, 'userIndex'])->name('absence.user.index');
        Route::get('/{id}', [AbsenceController::class, 'show'])->name('absence.show');
        Route::post('/', [AbsenceController::class, 'store'])->name('absence.store');
        Route::patch('/{id}', [AbsenceController::class, 'update'])->name('absence.update');
        Route::delete('/{id}', [AbsenceController::class, 'destroy'])->name('absence.destroy');
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
    Route::patch('/semester/m/update/{id}', [SemesterMealTypeController::class, 'update']);
    Route::patch('/weekend/m/update/{id}', [WeekendMealTypeController::class, 'update']);

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

    Route::prefix('apply')->group(function () {
        Route::post('/weekend/auto', [RestaurantApplyDivisionController::class, 'onWeekendAuto']);
        Route::patch('/weekend/set', [RestaurantApplyDivisionController::class, 'setWeekendAuto']);
        Route::get('/weekend/get', [RestaurantApplyDivisionController::class, 'getWeekendAuto']);

        Route::post('/semester/auto', [RestaurantApplyDivisionController::class, 'onSemesterAuto']);
        Route::patch('/semester/set', [RestaurantApplyDivisionController::class, 'setSemesterAuto']);
        Route::get('/semester/get', [RestaurantApplyDivisionController::class, 'getSemesterAuto']);

        Route::post('/manual', [RestaurantApplyDivisionController::class, 'onManual']);
        Route::patch('/manual/set', [RestaurantApplyDivisionController::class, 'setManual']);
        Route::get('/manual/get', [RestaurantApplyDivisionController::class, 'getManual']);
        Route::get('/manual/get/app', [RestaurantApplyDivisionController::class, 'getManualApp']);

        Route::get('/state', [RestaurantApplyDivisionController::class, 'showApplyState']);
        Route::post('/state/on', [RestaurantApplyDivisionController::class, 'onApplyState']);

        Route::get('/state/check/semester', [RestaurantApplyDivisionController::class, 'semesterCheck']);
        Route::get('/state/check/weekend', [RestaurantApplyDivisionController::class, 'weekendCheck']);
        Route::get('/state/web/semester', [RestaurantApplyDivisionController::class, 'webSemester']);
        Route::get('/state/web/weekend', [RestaurantApplyDivisionController::class, 'webWeekend']);


    });
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


