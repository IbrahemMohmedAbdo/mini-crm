<?php

namespace App\Policies;

use App\Models\Action;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ActionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admins and employees can view the list of actions (scope is handled in resource query)
        return $user->role === 'admin' || $user->role === 'employee';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Action $action): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        if ($user->role === 'employee' && $user->employee) {
            // Employee can view if they performed the action or if the action is for a customer assigned to them
            return $action->employee_id === $user->employee->id || $action->customer->assigned_employee_id === $user->employee->id;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin' || $user->role === 'employee';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Action $action): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        if ($user->role === 'employee' && $user->employee) {
            // Employee can update only actions they performed
            return $action->employee_id === $user->employee->id;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Action $action): bool
    {
        // Only admins can delete actions
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Action $action): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Action $action): bool
    {
        return $user->role === 'admin';
    }
}

