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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->unsignedInteger('employee_no');
            $table->foreignId('head_office_id')->constrained();
            $table->foreignId('branch_office_id')->constrained();
            $table->foreignId('shift_id')->nullable()->constrained('type_values');
            $table->date('date_of_birth')->nullable();
            $table->foreignId('marital_id')->nullable()->constrained('type_values');
            $table->foreignId('district_id')->nullable();
            $table->foreignId('employee_type')->nullable()->constrained('type_values');
            $table->date('leave_date')->nullable();
            $table->string('cnic')->nullable();
            $table->string('phone_no')->nullable();
            $table->date('cnic_issuance')->nullable();
            $table->date('cnic_expiry')->nullable();
            $table->string('personal_email')->nullable();
            $table->string('offical_email')->nullable();
            $table->foreignId('department_id')->nullable()->constrained('type_values');
            $table->foreignId('report_to_id')->nullable()->constrained('designations');
            $table->foreignId('blood_group')->nullable()->constrained('type_values');
            $table->date('date_of_joining')->nullable();
            $table->foreignId('designation_id')->nullable()->constrained();
            $table->foreignId('parentage_id')->nullable()->constrained('type_values');
            $table->string('parentage_name')->nullable();
            $table->foreignId('religion_id')->nullable()->constrained('type_values');
            $table->string('emergency_no')->nullable();
            $table->foreignId('gender_id')->nullable()->constrained('type_values');
            $table->foreignId('reference_id')->nullable()->constrained('type_values');
            $table->string('reference')->nullable();
            $table->text('residential_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('emp_profile')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
