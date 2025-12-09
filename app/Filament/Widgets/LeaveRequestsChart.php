<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Infrastructure\Persistence\Eloquent\Models\LeaveRequest;

class LeaveRequestsChart extends ChartWidget
{
    protected static ?string $heading = 'Leave Requests by Status';
    
    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $leavesByStatus = LeaveRequest::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Leave Requests',
                    'data' => array_values($leavesByStatus),
                    'backgroundColor' => [
                        'rgba(251, 191, 36, 0.6)',  // Pending - warning
                        'rgba(34, 197, 94, 0.6)',   // Approved - success
                        'rgba(239, 68, 68, 0.6)',   // Rejected - danger
                        'rgba(156, 163, 175, 0.6)', // Cancelled - gray
                    ],
                ],
            ],
            'labels' => array_keys($leavesByStatus),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}

