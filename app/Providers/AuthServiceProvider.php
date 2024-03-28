<?php

namespace App\Providers;

use App\Models\AbsenceList;
use App\Models\AfterService;
use App\Models\AfterServiceComment;
use App\Models\MeetingRoom;
use App\Models\MeetingRoomReservation;
use App\Models\Notice;
use App\Models\SalonBreakTime;
use App\Models\SalonBusinessHour;
use App\Models\SalonCategory;
use App\Models\SalonReservation;
use App\Models\SalonService;
use App\Policies\AdminPolicy;
use App\Policies\SalonPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 관리자
        AbsenceList::class => AdminPolicy::class,
        AfterServiceComment::class => AdminPolicy::class,
        AfterService::class => AdminPolicy::class,
        MeetingRoom::class => AdminPolicy::class,
        MeetingRoomReservation::class => AdminPolicy::class,
        Notice::class => AdminPolicy::class,

        // 미용실
        SalonBreakTime::class => SalonPolicy::class,
        SalonBusinessHour::class => SalonPolicy::class,
        SalonCategory::class => SalonPolicy::class,
        SalonReservation::class => SalonPolicy::class,
        SalonService::class => SalonPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // users 가드를 사용할 때 해당 커스텀 프로바이더를 통해 인증함
        Auth::provider('users', function ($app, array $config) {
            // Return an instance of Illuminate\Contracts\Auth\CustomUserProvider...

            return new CustomUserProvider();
        });
    }
}
