<?php

declare(strict_types=1);

namespace App\Support\Logging;

use Illuminate\Support\Facades\Log;

/**
 * Security logger for authentication and authorization events
 */
class SecurityLogger
{
    /**
     * Log a security event
     */
    public static function log(
        string $eventType,
        string|int|null $userId = null,
        ?array $context = []
    ): void {
        Log::channel('security')->info($eventType, array_merge($context, [
            'user_id' => $userId ?? auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ]));
    }

    /**
     * Log successful login
     */
    public static function loginSuccess(string|int $userId, string $email): void
    {
        self::log('auth.login.success', (string) $userId, [
            'email' => $email,
        ]);
    }

    /**
     * Log failed login attempt
     */
    public static function loginFailure(string $email, string $reason = 'Invalid credentials'): void
    {
        self::log('auth.login.failure', null, [
            'email' => $email,
            'reason' => $reason,
        ]);
    }

    /**
     * Log logout
     */
    public static function logout(string|int $userId): void
    {
        self::log('auth.logout', (string) $userId);
    }

    /**
     * Log account locked
     */
    public static function accountLocked(string|int $userId, string $email): void
    {
        self::log('auth.account_locked', (string) $userId, [
            'email' => $email,
            'severity' => 'warning',
        ]);
    }

    /**
     * Log password change
     */
    public static function passwordChanged(string|int $userId): void
    {
        self::log('auth.password_changed', (string) $userId);
    }

    /**
     * Log unauthorized access attempt
     */
    public static function unauthorizedAccess(string $resource, string $action): void
    {
        self::log('auth.unauthorized', auth()->id(), [
            'resource' => $resource,
            'action' => $action,
            'severity' => 'warning',
        ]);
    }

    /**
     * Log impersonation started
     */
    public static function impersonationStarted(
        string|int $adminId,
        string $adminEmail,
        string|int $targetUserId,
        string $targetUserEmail,
        string $employeeId
    ): void {
        self::log('auth.impersonation.started', (string) $adminId, [
            'admin_email' => $adminEmail,
            'target_user_id' => (string) $targetUserId,
            'target_user_email' => $targetUserEmail,
            'employee_id' => $employeeId,
            'severity' => 'info',
        ]);
    }

    /**
     * Log impersonation stopped
     */
    public static function impersonationStopped(
        string|int $adminId,
        string $adminEmail,
        string|int $targetUserId,
        string $targetUserEmail
    ): void {
        self::log('auth.impersonation.stopped', (string) $adminId, [
            'admin_email' => $adminEmail,
            'target_user_id' => (string) $targetUserId,
            'target_user_email' => $targetUserEmail,
            'severity' => 'info',
        ]);
    }
}

