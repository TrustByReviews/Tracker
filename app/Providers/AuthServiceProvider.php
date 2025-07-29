<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\PaymentReportPolicy;
use App\Providers\CustomUserProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        PaymentReportPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Registrar el provider personalizado para manejar IDs legacy
        Auth::provider('custom-users', function ($app, array $config) {
            return new CustomUserProvider($app['hash'], $config['model']);
        });
        
        Gate::define('viewPaymentReports', [PaymentReportPolicy::class, 'viewPaymentReports']);
        Gate::define('generatePaymentReports', [PaymentReportPolicy::class, 'generatePaymentReports']);
    }
} 