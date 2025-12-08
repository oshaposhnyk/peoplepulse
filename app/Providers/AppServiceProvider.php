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
            \Infrastructure\Persistence\Eloquent\Repositories\LeaveRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Schedule monthly leave accrual
        $this->app->booted(function () {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
            
            // Run monthly leave accrual on 1st day of month at midnight
            $schedule->job(new \App\Jobs\AccrueLeaveBalances())
                ->monthlyOn(1, '00:00')
                ->name('accrue-leave-balances')
                ->withoutOverlapping();
        });
    }
}
