<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use App\Policies\EmployeePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \Infrastructure\Persistence\Eloquent\Models\Employee::class => \App\Policies\EmployeePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Define gates for specific permissions
        
        Gate::define('manage-employees', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-teams', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-equipment', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('approve-leave', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('view-audit-logs', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-users', function (User $user) {
            return $user->isAdmin();
        });
    }
}

