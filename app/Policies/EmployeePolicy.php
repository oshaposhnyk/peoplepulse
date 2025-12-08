<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class EmployeePolicy
{
    /**
     * Determine if user can view any employees
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if user can view the employee
     */
    public function view(User $user, $employee): bool
    {
        // Admin can view any employee
        if ($user->isAdmin()) {
            return true;
        }

        // Employee can view own profile
        return $user->employee_id === $employee->id;
    }

    /**
     * Determine if user can create employees
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if user can update the employee
     */
    public function update(User $user, $employee): bool
    {
        // Admin can update any employee
        if ($user->isAdmin()) {
            return true;
        }

        // Employee can update own profile (limited fields)
        return $user->employee_id === $employee->id;
    }

    /**
     * Determine if user can delete the employee
     */
    public function delete(User $user, $employee): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if user can terminate the employee
     */
    public function terminate(User $user, $employee): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if user can view salary
     */
    public function viewSalary(User $user, $employee): bool
    {
        // Admin can view any salary
        if ($user->isAdmin()) {
            return true;
        }

        // Employee can view own salary
        return $user->employee_id === $employee->id;
    }

    /**
     * Determine if user can change position
     */
    public function changePosition(User $user, $employee): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if user can change location
     */
    public function changeLocation(User $user, $employee): bool
    {
        return $user->isAdmin();
    }
}

