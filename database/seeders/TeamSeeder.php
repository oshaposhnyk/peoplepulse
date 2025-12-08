<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Infrastructure\Persistence\Eloquent\Models\Employee;
use Infrastructure\Persistence\Eloquent\Models\Team;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        // Create parent teams
        $engineeringTeam = Team::factory()->create([
            'name' => 'Engineering',
            'type' => 'Development',
            'department' => 'Engineering',
            'team_id' => 'TEAM-0001',
        ]);

        $qaTeam = Team::factory()->create([
            'name' => 'Quality Assurance',
            'type' => 'QA',
            'department' => 'Engineering',
            'team_id' => 'TEAM-0002',
        ]);

        // Create child teams
        $backendTeam = Team::factory()->withParent($engineeringTeam)->create([
            'name' => 'Backend Engineering',
            'type' => 'Development',
            'team_id' => 'TEAM-0003',
        ]);

        $frontendTeam = Team::factory()->withParent($engineeringTeam)->create([
            'name' => 'Frontend Engineering',
            'type' => 'Development',
            'team_id' => 'TEAM-0004',
        ]);

        // Create 6 more teams
        Team::factory()->count(6)->create();

        // Assign members to teams
        $employees = Employee::active()->limit(30)->get();
        
        foreach ($employees as $index => $employee) {
            $team = match($index % 4) {
                0 => $backendTeam,
                1 => $frontendTeam,
                2 => $qaTeam,
                default => $engineeringTeam,
            };

            $team->members()->attach($employee->id, [
                'role' => $index % 10 === 0 ? 'TeamLead' : 'Member',
                'allocation_percentage' => 100,
                'assigned_at' => now(),
            ]);
        }

        $this->command->info('Created 10 teams with 30 team member assignments');
    }
}

