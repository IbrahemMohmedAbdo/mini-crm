<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone_number',
    ];

    /**
     * Get the user that owns the employee profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the customers assigned to this employee.
     */
    public function assignedCustomers(): HasMany
    {
        return $this->hasMany(Customer::class, 'assigned_employee_id');
    }

    /**
     * Get the customers added by this employee.
     */
    public function addedCustomers(): HasMany
    {
        return $this->hasMany(Customer::class, 'added_by_employee_id');
    }

    /**
     * Get the actions performed by this employee.
     */
    public function actions(): HasMany
    {
        return $this->hasMany(Action::class);
    }
}
