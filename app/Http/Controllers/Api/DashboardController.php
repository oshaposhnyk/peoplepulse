<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Infrastructure\Persistence\Eloquent\Models\Employee;
use Infrastructure\Persistence\Eloquent\Models\Equipment;
use Infrastructure\Persistence\Eloquent\Models\LeaveRequest;
use Infrastructure\Persistence\Eloquent\Models\Team;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'totalEmployees' => Employee::count(),
            'activeEmployees' => Employee::active()->count(),
            'activeTeams' => Team::active()->count(),
            'totalEquipment' => Equipment::count(),
            'assignedEquipment' => Equipment::assigned()->count(),
            'availableEquipment' => Equipment::available()->count(),
            'pendingLeaves' => LeaveRequest::pending()->count(),
            'approvedLeaves' => LeaveRequest::approved()->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}

