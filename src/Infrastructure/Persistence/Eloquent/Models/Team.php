<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Team extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected static function newFactory()
    {
        return \Database\Factories\TeamFactory::new();
    }

    protected $fillable = [
        'team_id',
        'name',
        'description',
        'type',
        'department',
        'parent_team_id',
        'max_size',
        'is_active',
        'disbanded_at',
        'disbanded_reason',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'disbanded_at' => 'datetime',
        'max_size' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    /**
     * Parent team relationship
     */
    public function parent()
    {
        return $this->belongsTo(Team::class, 'parent_team_id');
    }

    /**
     * Child teams relationship
     */
    public function children()
    {
        return $this->hasMany(Team::class, 'parent_team_id');
    }

    /**
     * Current team members (not removed)
     */
    public function members()
    {
        return $this->belongsToMany(
            Employee::class,
            'team_members',
            'team_id',
            'employee_id'
        )
        ->withPivot(['role', 'allocation_percentage', 'assigned_at', 'removed_at'])
        ->whereNull('team_members.removed_at')
        ->withTimestamps();
    }

    /**
     * All team members including removed
     */
    public function allMembers()
    {
        return $this->belongsToMany(
            Employee::class,
            'team_members',
            'team_id',
            'employee_id'
        )
        ->withPivot(['role', 'allocation_percentage', 'assigned_at', 'removed_at'])
        ->withTimestamps();
    }

    /**
     * Team lead
     */
    public function teamLead()
    {
        return $this->belongsToMany(
            Employee::class,
            'team_members',
            'team_id',
            'employee_id'
        )
        ->wherePivot('role', 'TeamLead')
        ->whereNull('team_members.removed_at')
        ->withPivot(['role', 'allocation_percentage', 'assigned_at'])
        ->first();
    }

    /**
     * Assignment history
     */
    public function assignmentHistory()
    {
        return $this->hasMany(TeamAssignmentHistory::class);
    }

    /**
     * Scope active teams
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->whereNull('disbanded_at');
    }

    /**
     * Scope search teams
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'ILIKE', "%{$search}%")
              ->orWhere('team_id', 'ILIKE', "%{$search}%");
        });
    }

    /**
     * Get member count attribute
     */
    public function getMemberCountAttribute(): int
    {
        return $this->members()->count();
    }
}

