<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Define admin gate - simple version
        Gate::define('admin', function($user) {
            // Simple debug
            error_log("Gate admin called with user role: " . ($user->role ?? 'undefined'));
            return ($user->role ?? '') === 'admin';
        });
    }
}
