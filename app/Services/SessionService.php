<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class SessionService
{
    /**
     * Get all active sessions for user
     */
    public function getActiveSessions(User $user): array
    {
        return DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderBy('last_activity', 'desc')
            ->get()
            ->map(function ($session) {
                $payload = json_decode(base64_decode($session->payload), true);
                
                return [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'user_agent' => $session->user_agent,
                    'last_activity' => date('Y-m-d H:i:s', $session->last_activity),
                    'is_current' => $session->id === session()->getId(),
                ];
            })
            ->toArray();
    }

    /**
     * Revoke specific session
     */
    public function revokeSession(User $user, string $sessionId): bool
    {
        $deleted = DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', $user->id)
            ->delete();

        return $deleted > 0;
    }

    /**
     * Revoke all sessions except current
     */
    public function revokeAllSessionsExceptCurrent(User $user): int
    {
        return DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', session()->getId())
            ->delete();
    }

    /**
     * Revoke all sessions
     */
    public function revokeAllSessions(User $user): int
    {
        return DB::table('sessions')
            ->where('user_id', $user->id)
            ->delete();
    }
}

