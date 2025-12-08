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

        SecurityLogger::loginSuccess($user->id, $user->email);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
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

        SecurityLogger::logout($request->user()->id);

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
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $request->user()->id,
                'email' => $request->user()->email,
                'role' => $request->user()->role,
                'employee' => $request->user()->employee,
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

            SecurityLogger::accountLocked($user->id, $user->email);
        }
    }
}

