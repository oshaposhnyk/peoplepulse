<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeLocationHistory extends Model
{
    public $timestamps = false;

    protected $table = 'employee_location_history';

    protected $fillable = [
        'employee_id',
        'previous_location',
        'new_location',
        'effective_date',
        'reason',
        'is_temporary',
        'expected_return_date',
        'created_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'expected_return_date' => 'date',
        'is_temporary' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}

