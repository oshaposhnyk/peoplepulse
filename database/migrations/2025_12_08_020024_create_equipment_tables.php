<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Business Keys
            $table->string('asset_tag', 30)->unique()->comment('ASSET-YYYY-XXXX format');
            $table->string('serial_number', 100)->unique();
            
            // Equipment Information
            $table->string('equipment_type', 50)->comment('Laptop, Desktop, Monitor, etc.');
            $table->string('brand', 100);
            $table->string('model', 100);
            
            // Specifications (JSON for flexibility)
            $table->json('specifications')->nullable()->comment('CPU, RAM, Storage, etc.');
            
            // Purchase Information
            $table->date('purchase_date');
            $table->decimal('purchase_price', 10, 2);
            $table->char('purchase_currency', 3)->default('USD');
            $table->string('supplier', 200)->nullable();
            
            // Warranty
            $table->date('warranty_expiry_date')->nullable();
            $table->string('warranty_provider', 200)->nullable();
            
            // Status
            $table->enum('status', ['Available', 'Assigned', 'InMaintenance', 'Decommissioned'])
                ->default('Available');
            $table->enum('condition', ['New', 'Good', 'Fair', 'Poor', 'Damaged'])
                ->default('New');
            
            // Current Assignment (denormalized for performance)
            $table->foreignId('current_assignee_id')->nullable()
                ->constrained('employees')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            
            // Location
            $table->string('physical_location', 200)->nullable()->comment('Office or warehouse location');
            
            // Decommission
            $table->timestamp('decommissioned_at')->nullable();
            $table->text('decommission_reason')->nullable();
            $table->string('disposal_method', 100)->nullable();
            
            // Metadata
            $table->timestamps();
            $table->softDeletes();
            $table->string('created_by', 20)->nullable();
            $table->string('updated_by', 20)->nullable();
            
            // Indexes
            $table->index('asset_tag');
            $table->index('serial_number');
            $table->index('equipment_type');
            $table->index('status');
            $table->index('current_assignee_id');
            $table->index('purchase_date');
        });

        Schema::create('equipment_assignments', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->foreignUuid('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            
            // Assignment Details
            $table->timestamp('assigned_at');
            $table->date('expected_return_date')->nullable();
            $table->timestamp('returned_at')->nullable();
            
            // Condition Tracking
            $table->enum('condition_at_issue', ['New', 'Good', 'Fair', 'Poor']);
            $table->enum('condition_at_return', ['New', 'Good', 'Fair', 'Poor', 'Damaged'])->nullable();
            
            // Accessories
            $table->json('accessories_issued')->nullable()->comment('List of accessories given');
            $table->json('accessories_returned')->nullable()->comment('List of accessories returned');
            
            // Damage
            $table->boolean('damage_reported')->default(false);
            $table->text('damage_description')->nullable();
            $table->json('damage_photos')->nullable()->comment('Array of photo URLs');
            $table->boolean('employee_liable')->nullable();
            
            // Digital Signature
            $table->string('employee_signature', 500)->nullable()->comment('Digital signature hash');
            
            // Metadata
            $table->string('issued_by', 20)->nullable();
            $table->string('received_by', 20)->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['equipment_id', 'returned_at']);
            $table->index(['employee_id', 'returned_at']);
            $table->index(['assigned_at', 'returned_at']);
        });

        Schema::create('equipment_maintenance', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key
            $table->foreignUuid('equipment_id')->constrained('equipment')->cascadeOnDelete();
            
            // Maintenance Details
            $table->string('maintenance_type', 50)->comment('Cleaning, Repair, Upgrade, etc.');
            $table->text('description');
            
            // Scheduling
            $table->date('scheduled_date');
            $table->date('completed_date')->nullable();
            $table->integer('expected_duration_days')->default(1);
            $table->integer('actual_duration_days')->nullable();
            
            // Service Provider
            $table->string('service_provider', 200);
            $table->boolean('is_external_vendor')->default(false);
            
            // Cost
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('actual_cost', 10, 2)->nullable();
            $table->char('cost_currency', 3)->default('USD');
            
            // Status
            $table->enum('status', ['Scheduled', 'InProgress', 'Completed', 'Cancelled'])
                ->default('Scheduled');
            
            // Work Details
            $table->text('work_performed')->nullable();
            $table->json('parts_replaced')->nullable();
            $table->boolean('warranty_work')->default(false);
            
            // Metadata
            $table->string('scheduled_by', 20)->nullable();
            $table->string('completed_by', 20)->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('equipment_id');
            $table->index('scheduled_date');
            $table->index('status');
        });

        Schema::create('equipment_transfers', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->foreignUuid('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->foreignId('from_employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('to_employee_id')->constrained('employees')->cascadeOnDelete();
            
            // Transfer Details
            $table->timestamp('transfer_date');
            $table->text('reason');
            $table->string('condition', 50);
            $table->boolean('data_wiped')->default(false);
            
            // Approval
            $table->boolean('requires_approval')->default(false);
            $table->string('approved_by', 20)->nullable();
            $table->timestamp('approved_at')->nullable();
            
            // Metadata
            $table->timestamp('created_at')->useCurrent();
            $table->string('created_by', 20)->nullable();
            
            // Indexes
            $table->index('equipment_id');
            $table->index('from_employee_id');
            $table->index('to_employee_id');
            $table->index('transfer_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_transfers');
        Schema::dropIfExists('equipment_maintenance');
        Schema::dropIfExists('equipment_assignments');
        Schema::dropIfExists('equipment');
    }
};
