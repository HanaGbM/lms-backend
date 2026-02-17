<?php

namespace App\Providers;

use App\Models\User;
use App\Services\EmailService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(EmailService::class, function ($app) {
            return new EmailService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Gate::define('viewPulse', function (User $user) {
        //     return true;
        // });
    }
}
