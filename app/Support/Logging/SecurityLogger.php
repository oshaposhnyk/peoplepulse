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
        ?string $userId = null,
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
    public static function loginSuccess(string $userId, string $email): void
    {
        self::log('auth.login.success', $userId, [
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
    public static function logout(string $userId): void
    {
        self::log('auth.logout', $userId);
    }

    /**
     * Log account locked
     */
    public static function accountLocked(string $userId, string $email): void
    {
        self::log('auth.account_locked', $userId, [
            'email' => $email,
            'severity' => 'warning',
        ]);
    }

    /**
     * Log password change
     */
    public static function passwordChanged(string $userId): void
    {
        self::log('auth.password_changed', $userId);
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
}

