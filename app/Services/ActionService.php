<?php

namespace App\Services;

use App\Models\Action;
use App\Models\Customer;
use App\Models\Employee;
use Carbon\Carbon;

class ActionService
{
    /**
     * Create a new action for a customer by an employee.
     *
     * @param Customer $customer
     * @param Employee $employee
     * @param array $data
     * @return Action
     */
    public function createAction(Customer $customer, Employee $employee, array $data): Action
    {
        return Action::create([
            'customer_id' => $customer->id,
            'employee_id' => $employee->id,
            'action_type' => $data['action_type'],
            'result' => $data['result'] ?? null,
            'action_date' => isset($data['action_date']) ? Carbon::parse($data['action_date']) : Carbon::now(),
        ]);
    }

    /**
     * Update an existing action.
     *
     * @param Action $action
     * @param array $data
     * @return Action
     */
    public function updateAction(Action $action, array $data): Action
    {
        $action->action_type = $data['action_type'] ?? $action->action_type;
        $action->result = $data['result'] ?? $action->result;
        $action->action_date = isset($data['action_date']) ? Carbon::parse($data['action_date']) : $action->action_date;
        $action->save();
        return $action;
    }
}

