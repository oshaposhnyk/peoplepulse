<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Infrastructure\Persistence\Eloquent\Models\Employee;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // Create 50 active employees
        Employee::factory()->count(50)->create();

        // Create 10 remote employees
        Employee::factory()->remote()->count(10)->create();

        // Create 15 hybrid employees
        Employee::factory()->hybrid()->count(15)->create();

        // Create 5 terminated employees
        Employee::factory()->terminated()->count(5)->create();

        $this->command->info('Created 80 employees (75 active, 5 terminated)');
    }
}

