<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Infrastructure\Persistence\Eloquent\Models\Employee;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        static $sequence = 0;
        $year = date('Y');
        $sequence++;
        
        return [
            'employee_id' => sprintf('EMP-%04d-%04d', $year, $sequence),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'middle_name' => fake()->optional()->firstName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'date_of_birth' => fake()->dateTimeBetween('-50 years', '-18 years'),
            
            'address_street' => fake()->streetAddress(),
            'address_city' => fake()->city(),
            'address_state' => fake()->stateAbbr(),
            'address_zip_code' => fake()->postcode(),
            'address_country' => 'USA',
            
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_phone' => fake()->phoneNumber(),
            'emergency_contact_relationship' => fake()->randomElement(['Spouse', 'Parent', 'Sibling', 'Friend']),
            
            'position' => fake()->randomElement([
                'Developer',
                'Senior Developer',
                'Lead Developer',
                'QA Engineer',
                'Senior QA Engineer',
                'DevOps Engineer',
                'Designer',
                'Product Manager',
                'Engineering Manager',
            ]),
            'department' => fake()->randomElement(['Engineering', 'QA', 'DevOps', 'Design', 'Product']),
            'employment_type' => fake()->randomElement(['Full-time', 'Part-time', 'Contract']),
            'employment_status' => 'Active',
            
            'salary_amount' => fake()->randomFloat(2, 60000, 150000),
            'salary_currency' => 'USD',
            'salary_frequency' => 'Annual',
            
            'office_location' => fake()->randomElement([
                'San Francisco HQ',
                'New York Office',
                'Austin Office',
                'London Office',
            ]),
            'work_location_type' => fake()->randomElement(['Office', 'Remote', 'Hybrid']),
            
            'remote_work_enabled' => fake()->boolean(30),
            'remote_work_policy' => null,
            
            'hire_date' => fake()->dateTimeBetween('-5 years', 'now'),
            'start_date' => fn(array $attributes) => $attributes['hire_date'],
            'termination_date' => null,
            'last_working_day' => null,
            'termination_type' => null,
            'termination_reason' => null,
            
            'photo_url' => null,
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    public function terminated(): self
    {
        return $this->state(fn (array $attributes) => [
            'employment_status' => 'Terminated',
            'termination_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'last_working_day' => fn(array $attrs) => $attrs['termination_date'],
            'termination_type' => fake()->randomElement(['Resignation', 'Termination', 'Retirement']),
            'termination_reason' => fake()->sentence(),
        ]);
    }

    public function remote(): self
    {
        return $this->state([
            'work_location_type' => 'Remote',
            'office_location' => 'Remote',
            'remote_work_enabled' => true,
            'remote_work_policy' => [
                'type' => 'FullRemote',
                'remoteDays' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            ],
        ]);
    }

    public function hybrid(): self
    {
        return $this->state([
            'work_location_type' => 'Hybrid',
            'remote_work_enabled' => true,
            'remote_work_policy' => [
                'type' => 'Hybrid',
                'remoteDays' => fake()->randomElements(
                    ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                    3
                ),
            ],
        ]);
    }
}

