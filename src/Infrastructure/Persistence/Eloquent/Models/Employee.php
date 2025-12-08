<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogsActivity;
use Spatie\Activitylog\Traits\LogsActivity as LogsActivityTrait;

class Employee extends Model
{
    use HasFactory, SoftDeletes, LogsActivityTrait;

    protected $fillable = [
        'employee_id',
        'first_name',
        'last_name',
        'middle_name',
        'email',
        'phone',
        'date_of_birth',
        'address_street',
        'address_city',
        'address_state',
        'address_zip_code',
        'address_country',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'position',
        'department',
        'employment_type',
        'employment_status',
        'salary_amount',
        'salary_currency',
        'salary_frequency',
        'office_location',
        'work_location_type',
        'remote_work_enabled',
        'remote_work_policy',
        'hire_date',
        'start_date',
        'termination_date',
        'last_working_day',
        'termination_type',
        'termination_reason',
        'photo_url',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'start_date' => 'date',
        'termination_date' => 'date',
        'last_working_day' => 'date',
        'salary_amount' => 'decimal:2',
        'remote_work_enabled' => 'boolean',
        'remote_work_policy' => 'array',
    ];

    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;

    /**
     * Relationship to user account
     */
    public function user()
    {
        return $this->hasOne(\App\Models\User::class, 'employee_id');
    }

    /**
     * Position history
     */
    public function positionHistory()
    {
        return $this->hasMany(EmployeePositionHistory::class)->orderBy('effective_date', 'desc');
    }

    /**
     * Location history
     */
    public function locationHistory()
    {
        return $this->hasMany(EmployeeLocationHistory::class)->orderBy('effective_date', 'desc');
    }

    /**
     * Scope active employees
     */
    public function scopeActive($query)
    {
        return $query->where('employment_status', 'Active');
    }

    /**
     * Scope terminated employees
     */
    public function scopeTerminated($query)
    {
        return $query->where('employment_status', 'Terminated');
    }

    /**
     * Scope search employees
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'ILIKE', "%{$search}%")
              ->orWhere('last_name', 'ILIKE', "%{$search}%")
              ->orWhere('email', 'ILIKE', "%{$search}%")
              ->orWhere('employee_id', 'ILIKE', "%{$search}%");
        });
    }

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute(): string
    {
        return $this->middle_name
            ? "{$this->first_name} {$this->middle_name} {$this->last_name}"
            : "{$this->first_name} {$this->last_name}";
    }
}

