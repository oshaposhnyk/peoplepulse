<?php

declare(strict_types=1);

namespace App\Support\Logging;

use Illuminate\Support\Facades\Log;

/**
 * Audit logger for tracking system changes
 */
class AuditLogger
{
    /**
     * Log an audit event
     */
    public static function log(
        string $eventType,
        string $resourceType,
        string $resourceId,
        string $action,
        ?array $changes = null,
        ?string $userId = null
    ): void {
        Log::channel('audit')->info($eventType, [
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'action' => $action,
            'changes' => $changes,
            'user_id' => $userId ?? auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Log employee action
     */
    public static function employee(string $action, string $employeeId, ?array $changes = null): void
    {
        self::log(
            "employee.{$action}",
            'Employee',
            $employeeId,
            $action,
            $changes
        );
    }

    /**
     * Log team action
     */
    public static function team(string $action, string $teamId, ?array $changes = null): void
    {
        self::log(
            "team.{$action}",
            'Team',
            $teamId,
            $action,
            $changes
        );
    }

    /**
     * Log equipment action
     */
    public static function equipment(string $action, string $equipmentId, ?array $changes = null): void
    {
        self::log(
            "equipment.{$action}",
            'Equipment',
            $equipmentId,
            $action,
            $changes
        );
    }

    /**
     * Log leave action
     */
    public static function leave(string $action, string $leaveId, ?array $changes = null): void
    {
        self::log(
            "leave.{$action}",
            'Leave',
            $leaveId,
            $action,
            $changes
        );
    }
}

