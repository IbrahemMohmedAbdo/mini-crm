<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Services\ActionService;
use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;

class ActionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(ActionService $actionService): void
    {
        $employeeUser = User::where("email", "employee@example.com")->first();
        $customerManagedByEmployee = Customer::where("name", "John Doe (Managed by Employee)")->first();
        $customerManagedByAdmin = Customer::where("name", "Jane Smith (Managed by Admin)")->first();

        if ($employeeUser && $employeeUser->employee && $customerManagedByEmployee) {
            $actionService->createAction($customerManagedByEmployee, $employeeUser->employee, [
                "action_type" => "call",
                "result" => "Initial contact call made, customer interested in product X.",
                "action_date" => Carbon::now()->subDays(5),
            ]);
            $actionService->createAction($customerManagedByEmployee, $employeeUser->employee, [
                "action_type" => "follow_up",
                "result" => "Followed up with email, sent brochure.",
                "action_date" => Carbon::now()->subDays(2),
            ]);
        }

        // Admin might log an action for a customer, possibly performed by an employee
        // For simplicity, let's assume admin logs an action performed by the sample employee for the customer admin manages
        if ($employeeUser && $employeeUser->employee && $customerManagedByAdmin) {
             $actionService->createAction($customerManagedByAdmin, $employeeUser->employee, [
                "action_type" => "visit",
                "result" => "Visited customer site, demo scheduled.",
                "action_date" => Carbon::now()->subDays(3),
            ]);
        }
    }
}

