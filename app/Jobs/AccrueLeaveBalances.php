<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Infrastructure\Persistence\Eloquent\Models\Employee;
use Infrastructure\Persistence\Eloquent\Models\LeaveBalance;

/**
 * Job to accrue monthly leave balances for all active employees
 */
class AccrueLeaveBalances implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 300;

    public function __construct()
    {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $year = date('Y');
        $month = date('m');
        $period = "{$year}-{$month}";

        $employees = Employee::active()->get();

        foreach ($employees as $employee) {
            $this->accrueForEmployee($employee, $year, $period);
        }

        logger()->info('Leave accrual completed', [
            'period' => $period,
            'employeesProcessed' => $employees->count(),
        ]);
    }

    private function accrueForEmployee(Employee $employee, int $year, string $period): void
    {
        $leaveTypes = [
            'Vacation' => 2.0,   // 2 days per month
            'Sick' => 1.0,       // 1 day per month
            'Personal' => 0.5,   // 0.5 days per month
        ];

        foreach ($leaveTypes as $type => $rate) {
            $balance = LeaveBalance::firstOrCreate(
                [
                    'employee_id' => $employee->id,
                    'year' => $year,
                    'leave_type' => $type,
                ],
                [
                    'accrual_rate' => $rate,
                    'max_carry_over' => $type === 'Vacation' ? 5 : 0,
                ]
            );

            // Accrue for the month
            $balance->increment('accrued', $rate);
            
            // Create accrual record
            $balance->accruals()->create([
                'employee_id' => $employee->id,
                'leave_type' => $type,
                'accrual_period' => $period,
                'accrued_days' => $rate,
                'balance_before' => $balance->available - $rate,
                'balance_after' => $balance->available,
                'accrual_type' => 'Scheduled',
                'accrued_at' => now(),
            ]);
        }
    }
}

