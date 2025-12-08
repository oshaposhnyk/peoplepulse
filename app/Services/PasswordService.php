<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PasswordService
{
    private const PASSWORD_HISTORY_LIMIT = 3;

    /**
     * Validate password against history
     */
    public function canUsePassword(User $user, string $newPassword): bool
    {
        $passwordHistory = $user->password_history ?? [];

        foreach ($passwordHistory as $oldPasswordHash) {
            if (Hash::check($newPassword, $oldPasswordHash)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Update password and maintain history
     */
    public function updatePassword(User $user, string $newPassword): void
    {
        // Add current password to history
        $passwordHistory = $user->password_history ?? [];
        $passwordHistory[] = $user->password;

        // Keep only last 3 passwords
        $passwordHistory = array_slice($passwordHistory, -self::PASSWORD_HISTORY_LIMIT);

        $user->update([
            'password' => Hash::make($newPassword),
            'password_changed_at' => now(),
            'password_history' => $passwordHistory,
            'failed_login_attempts' => 0,
        ]);

        // Revoke all existing tokens
        $user->tokens()->delete();
    }

    /**
     * Check if password meets complexity requirements
     */
    public function validateComplexity(string $password): bool
    {
        // Minimum 8 characters
        if (strlen($password) < 8) {
            return false;
        }

        // At least one uppercase
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }

        // At least one lowercase
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }

        // At least one digit
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }

        // At least one special character
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            return false;
        }

        return true;
    }
}

