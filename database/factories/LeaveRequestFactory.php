<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Infrastructure\Persistence\Eloquent\Models\Employee;
use Infrastructure\Persistence\Eloquent\Models\LeaveRequest;

class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        static $sequence = 0;
        $year = date('Y');
        $sequence++;
        
        $startDate = fake()->dateTimeBetween('now', '+3 months');
        $daysToAdd = fake()->numberBetween(1, 14);
        $endDate = (clone $startDate)->modify("+{$daysToAdd} days");
        $totalDays = $startDate->diff($endDate)->days + 1;

        return [
            'leave_id' => sprintf('LEAVE-%04d-%04d', $year, $sequence),
            'employee_id' => Employee::active()->inRandomOrder()->first()->id,
            'leave_type' => fake()->randomElement(['Vacation', 'Sick', 'Personal']),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => $totalDays,
            'working_days' => $totalDays,
            'reason' => fake()->sentence(),
            'contact_during_leave' => fake()->phoneNumber(),
            'backup_person_id' => null,
            'status' => 'Pending',
            'requested_at' => now(),
        ];
    }

    public function approved(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Approved',
            'approved_by' => Employee::active()->inRandomOrder()->first()->id,
            'approved_at' => now(),
        ]);
    }

    public function rejected(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Rejected',
            'rejected_by' => Employee::active()->inRandomOrder()->first()->id,
            'rejected_at' => now(),
            'rejection_reason' => fake()->sentence(),
        ]);
    }
}

