<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    protected $table = 'leave_balances';

    protected $fillable = [
        'employee_id',
        'year',
        'leave_type',
        'opening_balance',
        'accrued',
        'used',
        'pending',
        'adjusted',
        'carried_over',
        'forfeited',
        'accrual_rate',
        'max_carry_over',
        'max_balance',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:1',
        'accrued' => 'decimal:1',
        'used' => 'decimal:1',
        'pending' => 'decimal:1',
        'adjusted' => 'decimal:1',
        'carried_over' => 'decimal:1',
        'forfeited' => 'decimal:1',
        'accrual_rate' => 'decimal:2',
        'max_carry_over' => 'decimal:1',
        'max_balance' => 'decimal:1',
    ];

    protected $appends = ['available'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function accruals()
    {
        return $this->hasMany(LeaveAccrual::class);
    }

    /**
     * Calculate available balance
     */
    public function getAvailableAttribute(): float
    {
        return $this->opening_balance 
             + $this->accrued 
             + $this->adjusted 
             + $this->carried_over 
             - $this->used 
             - $this->pending 
             - $this->forfeited;
    }

    /**
     * Check if has sufficient balance
     */
    public function hasSufficientBalance(float $days): bool
    {
        return $this->available >= $days;
    }

    /**
     * Deduct from balance
     */
    public function deduct(float $days): void
    {
        $this->increment('used', $days);
        $this->decrement('pending', $days);
    }

    /**
     * Add to pending
     */
    public function addToPending(float $days): void
    {
        $this->increment('pending', $days);
    }

    /**
     * Remove from pending
     */
    public function removeFromPending(float $days): void
    {
        $this->decrement('pending', $days);
    }

    /**
     * Restore balance (on cancellation)
     */
    public function restore(float $days): void
    {
        $this->decrement('used', $days);
    }
}

