<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentMaintenance extends Model
{
    protected $table = 'equipment_maintenance';

    protected $fillable = [
        'equipment_id',
        'maintenance_type',
        'description',
        'scheduled_date',
        'completed_date',
        'expected_duration_days',
        'actual_duration_days',
        'service_provider',
        'is_external_vendor',
        'estimated_cost',
        'actual_cost',
        'cost_currency',
        'status',
        'work_performed',
        'parts_replaced',
        'warranty_work',
        'scheduled_by',
        'completed_by',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'date',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'parts_replaced' => 'array',
        'is_external_vendor' => 'boolean',
        'warranty_work' => 'boolean',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }
}

