<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Infrastructure\Persistence\Eloquent\Models\Employee;
use Infrastructure\Persistence\Eloquent\Models\LeaveBalance;
use Infrastructure\Persistence\Eloquent\Models\LeaveRequest;

class LeaveSeeder extends Seeder
{
    public function run(): void
    {
        $year = date('Y');

        // Create leave balances for all active employees
        $employees = Employee::active()->get();

        foreach ($employees as $employee) {
            // Vacation balance
            LeaveBalance::create([
                'employee_id' => $employee->id,
                'year' => $year,
                'leave_type' => 'Vacation',
                'opening_balance' => 0,
                'accrued' => 24, // 2 days/month * 12 months
                'used' => fake()->numberBetween(0, 10),
                'pending' => 0,
                'adjusted' => 0,
                'carried_over' => fake()->numberBetween(0, 5),
                'forfeited' => 0,
                'accrual_rate' => 2.0,
                'max_carry_over' => 5,
            ]);

            // Sick leave balance
            LeaveBalance::create([
                'employee_id' => $employee->id,
                'year' => $year,
                'leave_type' => 'Sick',
                'opening_balance' => 0,
                'accrued' => 12, // 1 day/month * 12 months
                'used' => fake()->numberBetween(0, 5),
                'pending' => 0,
                'adjusted' => 0,
                'carried_over' => 0,
                'forfeited' => 0,
                'accrual_rate' => 1.0,
                'max_carry_over' => 0,
            ]);

            // Personal days
            LeaveBalance::create([
                'employee_id' => $employee->id,
                'year' => $year,
                'leave_type' => 'Personal',
                'opening_balance' => 0,
                'accrued' => 6, // 0.5 days/month * 12 months
                'used' => fake()->numberBetween(0, 3),
                'pending' => 0,
                'adjusted' => 0,
                'carried_over' => 0,
                'forfeited' => 0,
                'accrual_rate' => 0.5,
                'max_carry_over' => 0,
            ]);
        }

        // Create 30 leave requests
        LeaveRequest::factory()->count(20)->create();
        LeaveRequest::factory()->approved()->count(7)->create();
        LeaveRequest::factory()->rejected()->count(3)->create();

        $this->command->info("Created leave balances for {$employees->count()} employees and 30 leave requests");
    }
}

