<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\User;

class CustomerService
{
    /**
     * Create a new customer.
     *
     * @param array $data
     * @param Employee|null $addedByEmployee The employee who is adding this customer (optional).
     * @return Customer
     */
    public function createCustomer(array $data, ?Employee $addedByEmployee = null): Customer
    {
        return Customer::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone_number' => $data['phone_number'] ?? null,
            'company' => $data['company'] ?? null,
            'address' => $data['address'] ?? null,
            'added_by_employee_id' => $addedByEmployee?->id,
            'assigned_employee_id' => $data['assigned_employee_id'] ?? null,
        ]);
    }

    /**
     * Update an existing customer.
     *
     * @param Customer $customer
     * @param array $data
     * @return Customer
     */
    public function updateCustomer(Customer $customer, array $data): Customer
    {
        $customer->fill($data);
        $customer->save();
        return $customer;
    }

    /**
     * Assign a customer to an employee.
     *
     * @param Customer $customer
     * @param Employee $employee
     * @return Customer
     */
    public function assignCustomerToEmployee(Customer $customer, Employee $employee): Customer
    {
        $customer->assigned_employee_id = $employee->id;
        $customer->save();
        return $customer;
    }
}

