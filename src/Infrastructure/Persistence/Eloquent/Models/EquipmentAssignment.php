<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentAssignment extends Model
{
    protected $table = 'equipment_assignments';

    protected $fillable = [
        'equipment_id',
        'employee_id',
        'assigned_at',
        'expected_return_date',
        'returned_at',
        'condition_at_issue',
        'condition_at_return',
        'accessories_issued',
        'accessories_returned',
        'damage_reported',
        'damage_description',
        'damage_photos',
        'employee_liable',
        'employee_signature',
        'issued_by',
        'received_by',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'expected_return_date' => 'date',
        'returned_at' => 'datetime',
        'accessories_issued' => 'array',
        'accessories_returned' => 'array',
        'damage_photos' => 'array',
        'damage_reported' => 'boolean',
        'employee_liable' => 'boolean',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function isActive(): bool
    {
        return $this->returned_at === null;
    }
}

