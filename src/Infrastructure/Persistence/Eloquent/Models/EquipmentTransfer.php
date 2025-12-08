<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentTransfer extends Model
{
    public $timestamps = false;

    protected $table = 'equipment_transfers';

    protected $fillable = [
        'equipment_id',
        'from_employee_id',
        'to_employee_id',
        'transfer_date',
        'reason',
        'condition',
        'data_wiped',
        'requires_approval',
        'approved_by',
        'approved_at',
        'created_by',
    ];

    protected $casts = [
        'transfer_date' => 'datetime',
        'approved_at' => 'datetime',
        'data_wiped' => 'boolean',
        'requires_approval' => 'boolean',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function fromEmployee()
    {
        return $this->belongsTo(Employee::class, 'from_employee_id');
    }

    public function toEmployee()
    {
        return $this->belongsTo(Employee::class, 'to_employee_id');
    }
}

