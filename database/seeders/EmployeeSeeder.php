<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Services\EmployeeService;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(EmployeeService $employeeService): void
    {
        // The UserSeeder already creates an employee user and their corresponding employee record.
        // If additional standalone employee records (not linked to new users) or more sample employees
        // are needed, they can be created here. For this task, we assume UserSeeder is sufficient
        // for the initial employee data.
        
        // Example: Find the employee user created by UserSeeder and ensure their employee profile exists
        $employeeUser = User::where("email", "employee@example.com")->first();
        if ($employeeUser && !$employeeUser->employee) {
            // This case should ideally be handled by UserSeeder ensuring employee creation.
            // However, as a fallback or for additional employees:
            // $employeeService->createEmployee($employeeUser, ["phone_number" => "000-000-0000"]);
        }
    }
}

