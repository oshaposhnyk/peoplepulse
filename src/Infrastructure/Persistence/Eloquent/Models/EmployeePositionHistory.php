<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePositionHistory extends Model
{
    public $timestamps = false;

    protected $table = 'employee_position_history';

    protected $fillable = [
        'employee_id',
        'previous_position',
        'new_position',
        'previous_department',
        'new_department',
        'previous_salary',
        'new_salary',
        'salary_currency',
        'salary_change_percentage',
        'effective_date',
        'reason',
        'approved_by',
        'created_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'previous_salary' => 'decimal:2',
        'new_salary' => 'decimal:2',
        'salary_change_percentage' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}

