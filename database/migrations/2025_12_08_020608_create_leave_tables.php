<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            
            // Business Key
            $table->string('leave_id', 30)->unique()->comment('LEAVE-YYYY-XXXX format');
            
            // Foreign Key
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            
            // Leave Details
            $table->enum('leave_type', ['Vacation', 'Sick', 'Unpaid', 'Bereavement', 'Parental', 'Personal']);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_days', 4, 1)->comment('Supports half days');
            $table->decimal('working_days', 4, 1);
            
            // Request Details
            $table->text('reason')->nullable();
            $table->string('contact_during_leave', 100)->nullable();
            $table->foreignId('backup_person_id')->nullable()
                ->constrained('employees')->nullOnDelete();
            
            // Status
            $table->enum('status', ['Pending', 'Approved', 'Rejected', 'Cancelled', 'Completed'])
                ->default('Pending');
            
            // Approval
            $table->foreignId('approved_by')->nullable()
                ->constrained('employees')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            
            // Rejection
            $table->foreignId('rejected_by')->nullable()
                ->constrained('employees')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Cancellation
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            
            // Metadata
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamps();
            
            // Indexes
            $table->index('employee_id');
            $table->index('leave_type');
            $table->index('status');
            $table->index(['start_date', 'end_date']);
            $table->index('approved_by');
        });

        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            
            // Balance Period
            $table->integer('year');
            
            // Leave Type
            $table->string('leave_type', 50);
            
            // Balance Tracking
            $table->decimal('opening_balance', 5, 1)->default(0);
            $table->decimal('accrued', 5, 1)->default(0);
            $table->decimal('used', 5, 1)->default(0);
            $table->decimal('pending', 5, 1)->default(0)->comment('In pending requests');
            $table->decimal('adjusted', 5, 1)->default(0)->comment('Manual adjustments');
            $table->decimal('carried_over', 5, 1)->default(0);
            $table->decimal('forfeited', 5, 1)->default(0);
            
            // Computed column for available balance
            // available = opening + accrued + adjusted + carried_over - used - pending - forfeited
            
            // Accrual Configuration
            $table->decimal('accrual_rate', 4, 2)->comment('Days per month');
            $table->decimal('max_carry_over', 5, 1)->default(0);
            $table->decimal('max_balance', 5, 1)->nullable()->comment('Maximum balance cap');
            
            // Metadata
            $table->timestamps();
            
            // Indexes
            $table->index('employee_id');
            $table->index(['employee_id', 'year', 'leave_type']);
            
            // Constraints
            $table->unique(['employee_id', 'year', 'leave_type']);
        });

        Schema::create('leave_accruals', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_balance_id')->constrained()->cascadeOnDelete();
            
            // Accrual Details
            $table->string('leave_type', 50);
            $table->string('accrual_period', 7)->comment('YYYY-MM format');
            $table->decimal('accrued_days', 5, 1);
            
            // Balance Snapshot
            $table->decimal('balance_before', 5, 1);
            $table->decimal('balance_after', 5, 1);
            
            // Accrual Type
            $table->enum('accrual_type', ['Scheduled', 'Manual', 'Adjustment', 'CarryOver'])
                ->default('Scheduled');
            $table->text('reason')->nullable();
            
            // Metadata
            $table->timestamp('accrued_at');
            $table->timestamp('created_at')->useCurrent();
            $table->string('created_by', 20)->nullable();
            
            // Indexes
            $table->index('employee_id');
            $table->index('accrual_period');
            $table->index('leave_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_accruals');
        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('leave_requests');
    }
};
