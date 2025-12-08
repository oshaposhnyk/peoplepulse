<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveAccrual extends Model
{
    public $timestamps = false;

    protected $table = 'leave_accruals';

    protected $fillable = [
        'employee_id',
        'leave_balance_id',
        'leave_type',
        'accrual_period',
        'accrued_days',
        'balance_before',
        'balance_after',
        'accrual_type',
        'reason',
        'accrued_at',
        'created_by',
    ];

    protected $casts = [
        'accrued_days' => 'decimal:1',
        'balance_before' => 'decimal:1',
        'balance_after' => 'decimal:1',
        'accrued_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveBalance()
    {
        return $this->belongsTo(LeaveBalance::class);
    }
}

