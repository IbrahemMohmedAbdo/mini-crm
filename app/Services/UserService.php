<?php

namespace App\Services;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserService
{
    /**
     * Create a new user and optionally an associated employee record.
     *
     * @param array $data
     * @param string $role
     * @return User
     */
    public function createUser(array $data, string $role = 'employee'): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'] ?? Str::random(10)), // Generate random password if not provided
            'role' => $role,
        ]);

        if ($role === 'employee') {
            Employee::create([
                'user_id' => $user->id,
                // Add other employee-specific fields from $data if necessary
                // e.g., 'phone_number' => $data['phone_number'] ?? null,
            ]);
        }

        return $user;
    }

    /**
     * Update an existing user.
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateUser(User $user, array $data): User
    {
        $user->name = $data['name'] ?? $user->name;
        $user->email = $data['email'] ?? $user->email;

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        
        if (isset($data['role'])) {
            $user->role = $data['role'];
        }

        $user->save();
        
        // Handle employee specific data if role is employee and data is provided
        if ($user->role === 'employee' && $user->employee) {
            if(isset($data['phone_number'])){
                 $user->employee->phone_number = $data['phone_number'];
                 $user->employee->save();
            }
        }

        return $user;
    }
}

