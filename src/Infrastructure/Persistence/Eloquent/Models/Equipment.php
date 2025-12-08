<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity as LogsActivityTrait;

class Equipment extends Model
{
    use HasFactory, HasUuids, SoftDeletes, LogsActivityTrait;

    protected $table = 'equipment';

    protected $fillable = [
        'asset_tag',
        'serial_number',
        'equipment_type',
        'brand',
        'model',
        'specifications',
        'purchase_date',
        'purchase_price',
        'purchase_currency',
        'supplier',
        'warranty_expiry_date',
        'warranty_provider',
        'status',
        'condition',
        'current_assignee_id',
        'assigned_at',
        'physical_location',
        'decommissioned_at',
        'decommission_reason',
        'disposal_method',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'specifications' => 'array',
        'purchase_date' => 'date',
        'warranty_expiry_date' => 'date',
        'purchase_price' => 'decimal:2',
        'assigned_at' => 'datetime',
        'decommissioned_at' => 'datetime',
    ];

    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;

    /**
     * Current assignee relationship
     */
    public function currentAssignee()
    {
        return $this->belongsTo(Employee::class, 'current_assignee_id');
    }

    /**
     * Assignment history
     */
    public function assignments()
    {
        return $this->hasMany(EquipmentAssignment::class)->orderBy('assigned_at', 'desc');
    }

    /**
     * Current assignment
     */
    public function currentAssignment()
    {
        return $this->hasOne(EquipmentAssignment::class)->whereNull('returned_at');
    }

    /**
     * Maintenance records
     */
    public function maintenanceRecords()
    {
        return $this->hasMany(EquipmentMaintenance::class)->orderBy('scheduled_date', 'desc');
    }

    /**
     * Transfers
     */
    public function transfers()
    {
        return $this->hasMany(EquipmentTransfer::class)->orderBy('transfer_date', 'desc');
    }

    /**
     * Scope available equipment
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'Available');
    }

    /**
     * Scope assigned equipment
     */
    public function scopeAssigned($query)
    {
        return $query->where('status', 'Assigned');
    }

    /**
     * Scope by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('equipment_type', $type);
    }
}

