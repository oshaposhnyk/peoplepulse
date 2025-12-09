<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Infrastructure\Persistence\Eloquent\Models\Employee;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create employees first
        $this->call([
            EmployeeSeeder::class,
            TeamSeeder::class,
            EquipmentSeeder::class,
            LeaveSeeder::class,
        ]);

        // Create admin user
        $adminEmployee = Employee::where('position', 'Engineering Manager')->first()
            ?? Employee::first();

        if ($adminEmployee) {
            User::create([
                'employee_id' => $adminEmployee->id,
                'email' => 'admin@peoplepulse.com',
                'password' => bcrypt('password'),
                'role' => 'Admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            $this->command->info('Admin user created: admin@peoplepulse.com / password');
        }

        // Create regular employee user
        $regularEmployee = Employee::where('position', 'Developer')
            ->where('id', '!=', $adminEmployee?->id)
            ->first();

        if ($regularEmployee) {
            User::create([
                'employee_id' => $regularEmployee->id,
                'email' => 'employee@peoplepulse.com',
                'password' => bcrypt('password'),
                'role' => 'Employee',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            $this->command->info('Employee user created: employee@peoplepulse.com / password');
        }
    }
}
