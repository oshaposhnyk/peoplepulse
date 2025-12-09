<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Infrastructure\Persistence\Eloquent\Models\Employee;
use Infrastructure\Persistence\Eloquent\Models\Team;
use Infrastructure\Persistence\Eloquent\Models\Equipment;
use Infrastructure\Persistence\Eloquent\Models\LeaveRequest;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('employment_status', 'Active')->count();
        $totalTeams = Team::count();
        $availableEquipment = Equipment::where('status', 'Available')->count();
        $pendingLeaves = LeaveRequest::where('status', 'Pending')->count();
        
        return [
            Stat::make('Total Employees', $totalEmployees)
                ->description($activeEmployees . ' active')
                ->descriptionIcon('heroicon-m-users')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),
                
            Stat::make('Teams', $totalTeams)
                ->description('Active teams')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
                
            Stat::make('Available Equipment', $availableEquipment)
                ->description('Ready for assignment')
                ->descriptionIcon('heroicon-m-computer-desktop')
                ->color('warning'),
                
            Stat::make('Pending Leaves', $pendingLeaves)
                ->description('Awaiting approval')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('danger'),
        ];
    }
}

