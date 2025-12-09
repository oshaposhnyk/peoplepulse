<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class LeaveRequest extends Model
{
    use HasFactory, LogsActivity;

    protected static function newFactory()
    {
        return \Database\Factories\LeaveRequestFactory::new();
    }

    protected $table = 'leave_requests';

    protected $fillable = [
        'leave_id',
        'employee_id',
        'leave_type',
        'start_date',
        'end_date',
        'total_days',
        'working_days',
        'reason',
        'contact_during_leave',
        'backup_person_id',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'cancelled_at',
        'cancellation_reason',
        'requested_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_days' => 'decimal:1',
        'working_days' => 'decimal:1',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'requested_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function backupPerson()
    {
        return $this->belongsTo(Employee::class, 'backup_person_id');
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    public function rejecter()
    {
        return $this->belongsTo(Employee::class, 'rejected_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'Approved');
    }

    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate])
              ->orWhere(function ($q2) use ($startDate, $endDate) {
                  $q2->where('start_date', '<=', $startDate)
                     ->where('end_date', '>=', $endDate);
              });
        });
    }
}

