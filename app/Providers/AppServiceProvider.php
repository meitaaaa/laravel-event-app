<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Registration;
use App\Observers\RegistrationObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register RegistrationObserver to monitor and protect attendance_token
        Registration::observe(RegistrationObserver::class);
    }
}
