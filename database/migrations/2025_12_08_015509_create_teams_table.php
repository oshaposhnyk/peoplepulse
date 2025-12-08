<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            
            // Business Key
            $table->string('team_id', 20)->unique()->comment('TEAM-XXXX format');
            
            // Team Information
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('type', 50)->comment('Development, QA, DevOps, etc.');
            $table->string('department', 100);
            
            // Hierarchy
            $table->foreignId('parent_team_id')->nullable()
                ->constrained('teams')->nullOnDelete();
            
            // Configuration
            $table->integer('max_size')->nullable()->comment('Maximum number of members');
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->timestamp('disbanded_at')->nullable();
            $table->text('disbanded_reason')->nullable();
            
            // Metadata
            $table->timestamps();
            $table->softDeletes();
            $table->string('created_by', 20)->nullable();
            $table->string('updated_by', 20)->nullable();
            
            // Indexes
            $table->index('name');
            $table->index('type');
            $table->index('department');
            $table->index('parent_team_id');
            $table->index('is_active');
        });

        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            
            // Member Information
            $table->enum('role', ['Member', 'TeamLead', 'TechLead'])->default('Member');
            $table->integer('allocation_percentage')->default(100);
            
            // Dates
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('removed_at')->nullable();
            
            // Metadata
            $table->timestamps();
            $table->string('assigned_by', 20)->nullable();
            $table->string('removed_by', 20)->nullable();
            $table->text('removal_reason')->nullable();
            
            // Indexes
            $table->index(['team_id', 'employee_id']);
            $table->index(['team_id', 'removed_at']);
            $table->index('role');
            
            // Constraints
            $table->unique(['team_id', 'employee_id', 'removed_at'], 'unique_active_membership');
        });

        Schema::create('team_assignment_history', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            
            // Assignment Details
            $table->string('role', 50);
            $table->integer('allocation_percentage');
            $table->timestamp('assigned_at');
            $table->timestamp('removed_at')->nullable();
            $table->integer('assignment_duration_days')->nullable();
            
            // Reason
            $table->text('assignment_reason')->nullable();
            $table->text('removal_reason')->nullable();
            
            // Metadata
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index(['team_id', 'employee_id']);
            $table->index(['assigned_at', 'removed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_assignment_history');
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('teams');
    }
};
