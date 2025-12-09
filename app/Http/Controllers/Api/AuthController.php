<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Models\User;
use App\Support\Logging\SecurityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        // Check if user exists
        if (!$user) {
            SecurityLogger::loginFailure($request->email, 'User not found');
            
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_CREDENTIALS',
                    'message' => 'Invalid email or password',
                ],
            ], 401);
        }

        // Check if account is locked
        if ($user->isLocked()) {
            SecurityLogger::loginFailure($request->email, 'Account locked');
            
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'ACCOUNT_LOCKED',
                    'message' => 'Account is locked. Please try again later.',
                ],
            ], 403);
        }

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            $this->recordFailedLogin($user, $request->email);
            
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_CREDENTIALS',
                    'message' => 'Invalid email or password',
                ],
            ], 401);
        }

        // Reset failed attempts
        $user->update([
            'failed_login_attempts' => 0,
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        // Generate token
        $token = $user->createToken('access-token')->plainTextToken;

        SecurityLogger::loginSuccess((string) $user->id, $user->email);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                    'employee_id' => $user->employee_id,
                    'employee_id_string' => $user->employee_id_string,
                    'employee' => $user->employee,
                ],
                'token' => $token,
                'tokenType' => 'Bearer',
                'expiresIn' => config('sanctum.expiration', 480) * 60,
            ],
            'message' => 'Login successful',
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        SecurityLogger::logout((string) $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $token = $user->currentAccessToken();
        
        $isImpersonating = false;
        $originalUser = null;
        
        // Check if currently impersonating
        if ($token && $token->abilities && in_array('impersonate', $token->abilities)) {
            $isImpersonating = true;
            
            // Extract original user ID from token abilities
            foreach ($token->abilities as $ability) {
                if (str_starts_with($ability, 'original_user_id:')) {
                    $originalUserId = (int) str_replace('original_user_id:', '', $ability);
                    $originalUserModel = User::find($originalUserId);
                    if ($originalUserModel) {
                        $originalUser = [
                            'id' => $originalUserModel->id,
                            'email' => $originalUserModel->email,
                            'role' => $originalUserModel->role,
                        ];
                    }
                    break;
                }
            }
        }
        
        // Get employee data with email
        $employee = null;
        if ($user->employee) {
            $employee = [
                'id' => $user->employee->employee_id,
                'name' => $user->employee->full_name,
                'email' => $user->employee->email, // Use email from employee profile
                'position' => $user->employee->position,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'email' => $employee ? $employee['email'] : $user->email, // Prefer employee email
                'role' => $user->role,
                'employee_id' => $user->employee_id,
                'employee_id_string' => $user->employee_id_string,
                'employee' => $employee,
                'isImpersonating' => $isImpersonating,
                'originalUser' => $originalUser,
            ],
        ]);
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request): JsonResponse
    {
        // Delete old token
        $request->user()->currentAccessToken()->delete();

        // Generate new token
        $token = $request->user()->createToken('access-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'tokenType' => 'Bearer',
                'expiresIn' => config('sanctum.expiration', 480) * 60,
            ],
            'message' => 'Token refreshed successfully',
        ]);
    }

    /**
     * Impersonate another user (Admin only)
     */
    public function impersonate(Request $request, string $employeeId): JsonResponse
    {
        $admin = $request->user();

        // Only admins can impersonate
        if (!$admin->isAdmin()) {
            SecurityLogger::unauthorizedAccess('impersonate', 'impersonate_user');
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Only administrators can impersonate users',
                ],
            ], 403);
        }

        // Find employee by employee_id
        $employee = \Infrastructure\Persistence\Eloquent\Models\Employee::where('employee_id', $employeeId)->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Employee not found',
                ],
            ], 404);
        }

        // Check if employee is active
        if ($employee->employment_status !== 'Active') {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_STATUS',
                    'message' => 'Can only impersonate active employees',
                ],
            ], 400);
        }

        // Find user associated with this employee, or create one if it doesn't exist
        $targetUser = User::where('employee_id', $employee->id)->first();

        if (!$targetUser) {
            // Auto-create user account for impersonation if it doesn't exist
            $targetUser = User::create([
                'employee_id' => $employee->id,
                'email' => $employee->email,
                'password' => Hash::make(uniqid('temp_', true)), // Temporary password, user will need to reset it
                'role' => 'Employee',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            SecurityLogger::log('auth.user_auto_created', (string) $admin->id, [
                'employee_id' => $employee->id,
                'employee_email' => $employee->email,
                'created_by_admin' => $admin->id,
                'reason' => 'impersonation',
            ]);
        }

        // Cannot impersonate yourself
        if ($targetUser->id === $admin->id) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_REQUEST',
                    'message' => 'Cannot impersonate yourself',
                ],
            ], 400);
        }

        // Store original admin info in token abilities
        $abilities = ['impersonate', 'original_user_id:' . $admin->id];

        // Create token for impersonated user with admin info in abilities
        $token = $targetUser->createToken('impersonation-token', $abilities)->plainTextToken;

        // Log impersonation
        SecurityLogger::impersonationStarted(
            (string) $admin->id,
            $admin->email,
            (string) $targetUser->id,
            $targetUser->email,
            $employeeId
        );

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $targetUser->id,
                    'email' => $targetUser->email,
                    'role' => $targetUser->role,
                    'employee_id' => $targetUser->employee_id,
                    'employee_id_string' => $targetUser->employee_id_string,
                    'employee' => $targetUser->employee,
                ],
                'originalUser' => [
                    'id' => $admin->id,
                    'email' => $admin->email,
                    'role' => $admin->role,
                ],
                'token' => $token,
                'tokenType' => 'Bearer',
                'expiresIn' => config('sanctum.expiration', 480) * 60,
                'isImpersonating' => true,
            ],
            'message' => 'Impersonation started successfully',
        ]);
    }

    /**
     * Stop impersonating and return to original user
     */
    public function stopImpersonating(Request $request): JsonResponse
    {
        $currentUser = $request->user();
        $token = $currentUser->currentAccessToken();

        // Check if currently impersonating
        if (!$token->abilities || !in_array('impersonate', $token->abilities)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_IMPERSONATING',
                    'message' => 'Not currently impersonating any user',
                ],
            ], 400);
        }

        // Extract original user ID from token abilities
        $originalUserId = null;
        foreach ($token->abilities as $ability) {
            if (str_starts_with($ability, 'original_user_id:')) {
                $originalUserId = (int) str_replace('original_user_id:', '', $ability);
                break;
            }
        }

        if (!$originalUserId) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_TOKEN',
                    'message' => 'Invalid impersonation token',
                ],
            ], 400);
        }

        // Find original admin user
        $originalUser = User::find($originalUserId);

        if (!$originalUser) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'ORIGINAL_USER_NOT_FOUND',
                    'message' => 'Original user not found',
                ],
            ], 404);
        }

        // Delete impersonation token
        $token->delete();

        // Log end of impersonation
        SecurityLogger::impersonationStopped(
            (string) $originalUser->id,
            $originalUser->email,
            (string) $currentUser->id,
            $currentUser->email
        );

        // Create new token for original admin
        $newToken = $originalUser->createToken('access-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $originalUser->id,
                    'email' => $originalUser->email,
                    'role' => $originalUser->role,
                    'employee_id' => $originalUser->employee_id,
                    'employee_id_string' => $originalUser->employee_id_string,
                    'employee' => $originalUser->employee,
                ],
                'token' => $newToken,
                'tokenType' => 'Bearer',
                'expiresIn' => config('sanctum.expiration', 480) * 60,
                'isImpersonating' => false,
            ],
            'message' => 'Impersonation stopped successfully',
        ]);
    }

    /**
     * Record failed login attempt
     */
    private function recordFailedLogin(User $user, string $email): void
    {
        $attempts = $user->failed_login_attempts + 1;

        $user->update([
            'failed_login_attempts' => $attempts,
        ]);

        SecurityLogger::loginFailure($email, 'Invalid password');

        // Lock account after 5 failed attempts
        if ($attempts >= 5) {
            $user->update([
                'is_locked' => true,
                'locked_until' => now()->addMinutes(30),
            ]);

            SecurityLogger::accountLocked((string) $user->id, $user->email);
        }
    }
}

