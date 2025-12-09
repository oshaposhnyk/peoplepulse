<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Infrastructure\Persistence\Eloquent\Models\Employee;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class EmployeeChart extends ChartWidget
{
    protected static ?string $heading = 'Employee Growth';
    
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        // Get employee count by hire date for the last 12 months
        $data = Trend::model(Employee::class)
            ->between(
                start: now()->subMonths(11),
                end: now(),
            )
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Employees hired',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => 'rgba(79, 70, 229, 0.2)',
                    'borderColor' => 'rgb(79, 70, 229)',
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}

