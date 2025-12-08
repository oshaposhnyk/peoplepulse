<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind repositories
        $this->app->bind(
            \Domain\Employee\Repositories\EmployeeRepositoryInterface::class,
            \Infrastructure\Persistence\Eloquent\Repositories\EmployeeRepository::class
        );

        $this->app->bind(
            \Domain\Team\Repositories\TeamRepositoryInterface::class,
            \Infrastructure\Persistence\Eloquent\Repositories\TeamRepository::class
        );

        $this->app->bind(
            \Domain\Equipment\Repositories\EquipmentRepositoryInterface::class,
            \Infrastructure\Persistence\Eloquent\Repositories\EquipmentRepository::class
        );

        $this->app->bind(
            \Domain\Leave\Repositories\LeaveRepositoryInterface::class,
            function () {
                // Will be implemented in Phase 8
                throw new \RuntimeException('LeaveRepository not yet implemented');
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
