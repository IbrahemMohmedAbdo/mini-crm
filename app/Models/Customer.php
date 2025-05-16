<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'company',
        'address',
        'added_by_employee_id',
        'assigned_employee_id',
    ];

    /**
     * Get the employee who added this customer.
     */
    public function addedByEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'added_by_employee_id');
    }

    /**
     * Get the employee to whom this customer is assigned.
     */
    public function assignedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_employee_id');
    }

    /**
     * Get the actions related to this customer.
     */
    public function actions(): HasMany
    {
        return $this->hasMany(Action::class);
    }
}
