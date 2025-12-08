<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            
            // Business Key
            $table->string('employee_id', 20)->unique()->comment('EMP-YYYY-XXXX format');
            
            // Personal Information
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('email')->unique();
            $table->string('phone', 20);
            $table->date('date_of_birth')->nullable();
            
            // Personal Address
            $table->string('address_street')->nullable();
            $table->string('address_city', 100)->nullable();
            $table->string('address_state', 100)->nullable();
            $table->string('address_zip_code', 20)->nullable();
            $table->string('address_country', 100)->default('USA');
            
            // Emergency Contact
            $table->string('emergency_contact_name', 200)->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->string('emergency_contact_relationship', 50)->nullable();
            
            // Employment Information
            $table->string('position', 100);
            $table->string('department', 100);
            $table->enum('employment_type', ['Full-time', 'Part-time', 'Contract', 'Intern'])
                ->default('Full-time');
            $table->enum('employment_status', ['Active', 'Terminated', 'OnLeave'])
                ->default('Active');
            
            // Compensation
            $table->decimal('salary_amount', 12, 2);
            $table->char('salary_currency', 3)->default('USD');
            $table->enum('salary_frequency', ['Annual', 'Monthly', 'Hourly'])->default('Annual');
            
            // Location
            $table->string('office_location', 100);
            $table->enum('work_location_type', ['Office', 'Remote', 'Hybrid'])->default('Office');
            
            // Remote Work
            $table->boolean('remote_work_enabled')->default(false);
            $table->json('remote_work_policy')->nullable()->comment('Remote work configuration');
            
            // Dates
            $table->date('hire_date');
            $table->date('start_date');
            $table->date('termination_date')->nullable();
            $table->date('last_working_day')->nullable();
            
            // Termination Details
            $table->string('termination_type', 50)->nullable();
            $table->text('termination_reason')->nullable();
            
            // Photo
            $table->string('photo_url', 500)->nullable();
            
            // Metadata
            $table->timestamps();
            $table->softDeletes();
            $table->string('created_by', 20)->nullable();
            $table->string('updated_by', 20)->nullable();
            
            // Indexes
            $table->index('email');
            $table->index('employment_status');
            $table->index('position');
            $table->index('department');
            $table->index('office_location');
            $table->index('hire_date');
        });

        Schema::create('employee_position_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            
            $table->string('previous_position', 100);
            $table->string('new_position', 100);
            $table->string('previous_department', 100);
            $table->string('new_department', 100);
            
            $table->decimal('previous_salary', 12, 2);
            $table->decimal('new_salary', 12, 2);
            $table->char('salary_currency', 3)->default('USD');
            $table->decimal('salary_change_percentage', 5, 2)->nullable();
            
            $table->date('effective_date');
            $table->text('reason');
            $table->string('approved_by', 20)->nullable();
            
            $table->timestamp('created_at')->useCurrent();
            $table->string('created_by', 20)->nullable();
            
            $table->index(['employee_id', 'effective_date']);
        });

        Schema::create('employee_location_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            
            $table->string('previous_location', 100);
            $table->string('new_location', 100);
            
            $table->date('effective_date');
            $table->text('reason');
            $table->boolean('is_temporary')->default(false);
            $table->date('expected_return_date')->nullable();
            
            $table->timestamp('created_at')->useCurrent();
            $table->string('created_by', 20)->nullable();
            
            $table->index(['employee_id', 'effective_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_location_history');
        Schema::dropIfExists('employee_position_history');
        Schema::dropIfExists('employees');
    }
};
