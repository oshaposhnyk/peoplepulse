<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'email',
        'password',
        'role',
        'locale',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_locked' => 'boolean',
            'locked_until' => 'datetime',
            'password_changed_at' => 'datetime',
            'last_login_at' => 'datetime',
            'mfa_enabled' => 'boolean',
            'mfa_backup_codes' => 'array',
        ];
    }

    /**
     * Get user's name for Filament
     */
    public function getNameAttribute(): string
    {
        return $this->email;
    }

    /**
     * Get employee ID string (EMP-XXXX-XXXX format)
     */
    public function getEmployeeIdStringAttribute(): ?string
    {
        return $this->employee?->employee_id;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'Admin';
    }

    /**
     * Check if user is employee
     */
    public function isEmployee(): bool
    {
        return $this->role === 'Employee';
    }

    /**
     * Check if account is locked
     */
    public function isLocked(): bool
    {
        if (!$this->is_locked) {
            return false;
        }

        // Auto-unlock if lock period expired
        if ($this->locked_until && $this->locked_until->isPast()) {
            $this->update([
                'is_locked' => false,
                'locked_until' => null,
            ]);
            return false;
        }

        return true;
    }

    /**
     * Relationship to employee
     */
    public function employee()
    {
        return $this->belongsTo(\Infrastructure\Persistence\Eloquent\Models\Employee::class, 'employee_id');
    }
}
