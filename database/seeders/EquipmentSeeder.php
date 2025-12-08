<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Infrastructure\Persistence\Eloquent\Models\Employee;
use Infrastructure\Persistence\Eloquent\Models\Equipment;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        // Create 50 laptops
        Equipment::factory()->laptop()->count(50)->create();

        // Create 30 monitors
        Equipment::factory()->count(30)->create([
            'equipment_type' => 'Monitor',
        ]);

        // Create 20 phones
        Equipment::factory()->count(20)->create([
            'equipment_type' => 'Phone',
        ]);

        // Create various accessories
        Equipment::factory()->count(40)->create([
            'equipment_type' => fake()->randomElement(['Keyboard', 'Mouse', 'Headset']),
        ]);

        // Assign some equipment to employees
        $employees = Employee::active()->limit(30)->get();
        $availableEquipment = Equipment::available()->limit(30)->get();

        foreach ($employees as $index => $employee) {
            if (isset($availableEquipment[$index])) {
                $equipment = $availableEquipment[$index];
                
                $equipment->update([
                    'status' => 'Assigned',
                    'current_assignee_id' => $employee->id,
                    'assigned_at' => now(),
                ]);

                $equipment->assignments()->create([
                    'employee_id' => $employee->id,
                    'assigned_at' => now(),
                    'condition_at_issue' => 'Good',
                    'issued_by' => 'SYSTEM',
                ]);
            }
        }

        $this->command->info('Created 140 equipment items (30 assigned to employees)');
    }
}

