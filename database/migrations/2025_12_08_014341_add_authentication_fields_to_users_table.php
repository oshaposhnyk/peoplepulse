<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Employee relationship
            $table->unsignedBigInteger('employee_id')->nullable()->after('id');
            
            // Remove name, we'll use employee relationship
            $table->dropColumn('name');
            
            // Role
            $table->enum('role', ['Admin', 'Employee'])->default('Employee')->after('email');
            
            // Account status
            $table->boolean('is_active')->default(true)->after('role');
            $table->boolean('is_locked')->default(false)->after('is_active');
            $table->timestamp('locked_until')->nullable()->after('is_locked');
            
            // Login tracking
            $table->integer('failed_login_attempts')->default(0)->after('locked_until');
            $table->timestamp('last_login_at')->nullable()->after('failed_login_attempts');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            
            // Password management
            $table->timestamp('password_changed_at')->nullable()->after('password');
            $table->timestamp('password_expires_at')->nullable()->after('password_changed_at');
            $table->json('password_history')->nullable()->after('password_expires_at')
                ->comment('Last 3 password hashes');
            
            // MFA
            $table->boolean('mfa_enabled')->default(false)->after('password_history');
            $table->string('mfa_secret')->nullable()->after('mfa_enabled');
            $table->json('mfa_backup_codes')->nullable()->after('mfa_secret');
            $table->timestamp('mfa_enabled_at')->nullable()->after('mfa_backup_codes');
            
            // Soft delete
            $table->softDeletes();
            
            // Indexes
            $table->index('employee_id');
            $table->index('role');
            $table->index('is_active');
            $table->index('is_locked');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'employee_id',
                'role',
                'is_active',
                'is_locked',
                'locked_until',
                'failed_login_attempts',
                'last_login_at',
                'last_login_ip',
                'password_changed_at',
                'password_expires_at',
                'password_history',
                'mfa_enabled',
                'mfa_secret',
                'mfa_backup_codes',
                'mfa_enabled_at',
                'deleted_at',
            ]);
            
            $table->string('name')->after('id');
        });
    }
};
