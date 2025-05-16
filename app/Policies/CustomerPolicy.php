<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admins and employees can view the list of customers (scope is handled in resource query)
        return $user->role === 'admin' || $user->role === 'employee';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Customer $customer): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        if ($user->role === 'employee' && $user->employee) {
            // Employee can view if they are assigned to the customer or added the customer
            return $customer->assigned_employee_id === $user->employee->id || $customer->added_by_employee_id === $user->employee->id;
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
    public function update(User $user, Customer $customer): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        if ($user->role === 'employee' && $user->employee) {
            // Employee can update if they are assigned to the customer or added the customer
            return $customer->assigned_employee_id === $user->employee->id || $customer->added_by_employee_id === $user->employee->id;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Customer $customer): bool
    {
        // Only admins can delete customers
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Customer $customer): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Customer $customer): bool
    {
        return $user->role === 'admin';
    }
}

