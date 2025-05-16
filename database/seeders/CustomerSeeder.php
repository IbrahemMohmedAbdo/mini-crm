<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Services\CustomerService;
use App\Models\User;
use App\Models\Employee;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(CustomerService $customerService): void
    {
        $employeeUser = User::where("email", "employee@example.com")->first();
        $adminUser = User::where("email", "admin@example.com")->first();

        if ($employeeUser && $employeeUser->employee) {
            $customerService->createCustomer([
                "name" => "John Doe (Managed by Employee)",
                "email" => "john.doe@example.com",
                "phone_number" => "555-1234",
                "company" => "Doe Industries",
                "address" => "123 Main St, Anytown, USA",
                "assigned_employee_id" => $employeeUser->employee->id, // Assigned to the employee user
            ], $employeeUser->employee); // Added by the employee user
        }

        if ($adminUser) {
            // Admin might add a customer and assign it to an employee, or not assign it.
            $employeeForAdminCustomer = Employee::first(); // Get any employee to assign
            $customerService->createCustomer([
                "name" => "Jane Smith (Managed by Admin)",
                "email" => "jane.smith@example.com",
                "phone_number" => "555-5678",
                "company" => "Smith Co.",
                "address" => "456 Oak Ave, Anytown, USA",
                "assigned_employee_id" => $employeeForAdminCustomer?->id, // Optionally assigned
            ]); // Added by admin (null for added_by_employee_id if not an employee)
        }
    }
}

