<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Infrastructure\Persistence\Eloquent\Models\Employee;
use Infrastructure\Persistence\Eloquent\Models\Equipment;
use Infrastructure\Persistence\Eloquent\Models\LeaveRequest;
use Infrastructure\Persistence\Eloquent\Models\Team;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            // Admin sees all statistics
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
        } else {
            // Employee sees only their own statistics
            $employee = $user->employee;
            
            if (!$employee) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'myPendingLeaves' => 0,
                        'myApprovedLeaves' => 0,
                        'myEquipment' => 0,
                        'myTeam' => null,
                    ],
                ]);
            }

            // Get employee's team
            $teamMember = \DB::table('team_members')
                ->join('teams', 'team_members.team_id', '=', 'teams.id')
                ->where('team_members.employee_id', $employee->id)
                ->whereNull('team_members.removed_at')
                ->select('teams.name')
                ->first();

            $stats = [
                'myPendingLeaves' => LeaveRequest::where('employee_id', $employee->id)
                    ->where('status', 'Pending')
                    ->count(),
                'myApprovedLeaves' => LeaveRequest::where('employee_id', $employee->id)
                    ->where('status', 'Approved')
                    ->count(),
                'myEquipment' => Equipment::where('current_assignee_id', $employee->id)
                    ->where('status', 'Assigned')
                    ->count(),
                'myTeam' => $teamMember ? $teamMember->name : null,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}

