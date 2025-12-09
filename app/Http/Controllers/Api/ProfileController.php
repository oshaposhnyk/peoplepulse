<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmployeeResource;
use Illuminate\Http\Request;
use Infrastructure\Persistence\Eloquent\Models\Employee;
use Infrastructure\Persistence\Eloquent\Repositories\EmployeeRepository;

class ProfileController extends Controller
{
    public function __construct(
        private readonly EmployeeRepository $employeeRepository
    ) {}

    /**
     * Get current user's profile
     */
    public function show(Request $request)
    {
        try {
            $user = $request->user();
            
            \Log::info('Profile show request', [
                'user_id' => $user->id,
                'user_employee_id' => $user->employee_id,
                'user_email' => $user->email
            ]);
            
            if (!$user->employee_id) {
                \Log::error('Profile show: No employee_id for user', ['user_id' => $user->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'No employee profile associated with this user',
                    'debug' => [
                        'user_id' => $user->id,
                        'employee_id' => $user->employee_id
                    ]
                ], 404);
            }

            // Get employee model from database by id (user->employee_id is the integer primary key)
            $employeeModel = \Infrastructure\Persistence\Eloquent\Models\Employee::where('id', $user->employee_id)->first();
            
            \Log::info('Profile show: Employee search result', [
                'user_employee_id' => $user->employee_id,
                'employee_found' => $employeeModel ? 'yes' : 'no',
                'employee_id_string' => $employeeModel ? $employeeModel->employee_id : null
            ]);
            
            if (!$employeeModel) {
                \Log::error('Profile show: Employee not found', [
                    'searching_for' => $user->employee_id,
                    'employee_count' => \Infrastructure\Persistence\Eloquent\Models\Employee::count()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Employee profile not found',
                    'debug' => [
                        'searching_for' => $user->employee_id,
                        'employee_count' => \Infrastructure\Persistence\Eloquent\Models\Employee::count()
                    ]
                ], 404);
            }

            // Use EmployeeResource for consistent formatting
            $employeeData = (new EmployeeResource($employeeModel))->toArray(request());
            
            // Get teams where this employee is a member
            $teams = \DB::table('team_members')
                ->join('teams', 'team_members.team_id', '=', 'teams.id')
                ->where('team_members.employee_id', $employeeModel->id)
                ->whereNull('team_members.removed_at')
                ->select('teams.team_id as id', 'teams.team_id', 'teams.name', 'team_members.role')
                ->get();
            
            $employeeData['teams'] = $teams;

            return response()->json([
                'success' => true,
                'data' => $employeeData
            ]);
        } catch (\Exception $e) {
            \Log::error('Profile show exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update current user's profile
     */
    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'phone' => 'nullable|string|max:20',
                'location' => 'nullable|string|max:100',
            ]);

            $user = $request->user();
            
            if (!$user->employee_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No employee profile associated with this user'
                ], 404);
            }

            $employee = Employee::where('id', $user->employee_id)->first();
            
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee profile not found'
                ], 404);
            }

            // Update only allowed fields
            if (isset($validated['phone'])) {
                $employee->phone = $validated['phone'];
            }
            
            if (isset($validated['location'])) {
                $employee->location = $validated['location'];
            }

            $employee->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $employee
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * View another user's profile (public profile)
     */
    public function viewProfile(Request $request, string $employeeId)
    {
        try {
            // Get employee model from database
            $employeeModel = \Infrastructure\Persistence\Eloquent\Models\Employee::where('employee_id', $employeeId)->first();
            
            if (!$employeeModel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ], 404);
            }

            // Use EmployeeResource for consistent formatting
            $employeeData = (new EmployeeResource($employeeModel))->toArray(request());
            
            // Get teams where this employee is a member
            $teams = \DB::table('team_members')
                ->join('teams', 'team_members.team_id', '=', 'teams.id')
                ->where('team_members.employee_id', $employeeModel->id)
                ->whereNull('team_members.removed_at')
                ->select('teams.team_id as id', 'teams.team_id', 'teams.name', 'team_members.role')
                ->get();
            
            $employeeData['teams'] = $teams;

            // Remove sensitive data for non-admin users
            $currentUser = $request->user();
            if (!$currentUser->isAdmin() && $currentUser->employee_id !== $employeeModel->id) {
                // Hide salary information for non-admin viewing other profiles
                unset($employeeData['salary']);
            }

            return response()->json([
                'success' => true,
                'data' => $employeeData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch employee profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
