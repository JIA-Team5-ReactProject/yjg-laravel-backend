<?php

namespace App\Providers;

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
        // 모든 정책은 컨벤션에 맞게 작성하여, DI 하도록 함
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
