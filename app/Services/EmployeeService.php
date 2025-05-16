<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;

class EmployeeService
{
    /**
     * Create a new employee linked to a user.
     *
     * @param User $user
     * @param array $data
     * @return Employee
     */
    public function createEmployee(User $user, array $data): Employee
    {
        return Employee::create([
            'user_id' => $user->id,
            'phone_number' => $data['phone_number'] ?? null,
            // Add other employee-specific fields from $data if necessary
        ]);
    }

    /**
     * Update an existing employee.
     *
     * @param Employee $employee
     * @param array $data
     * @return Employee
     */
    public function updateEmployee(Employee $employee, array $data): Employee
    {
        $employee->phone_number = $data['phone_number'] ?? $employee->phone_number;
        // Update other employee-specific fields
        $employee->save();

        // Optionally update associated user details if they are part of employee update form
        if (isset($data['name']) || isset($data['email'])) {
            $userData = [];
            if (isset($data['name'])) $userData['name'] = $data['name'];
            if (isset($data['email'])) $userData['email'] = $data['email'];
            // Password update should be handled carefully, perhaps in UserService or a dedicated Auth service
            app(UserService::class)->updateUser($employee->user, $userData);
        }

        return $employee;
    }
}

