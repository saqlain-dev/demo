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
        Schema::create('employee_requisitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->nullable()->constrained('employees');
            $table->foreignId('hiring_supervisor_id')->nullable()->constrained('employees');
            $table->string('is_budgeted')->nullable();
            $table->integer('department_id')->nullable();
            $table->string('job_title')->nullable();
            $table->text('request_reason')->nullable();
            $table->string('replacement_for_ms')->nullable();
            $table->foreignId('required_contract_type')->nullable()->constrained('type_values');
            $table->foreignId('required_job_type')->nullable()->constrained('type_values');
            $table->date('from_date')->nullable();
            $table->string('from_time')->nullable();
            $table->date('to_date')->nullable();
            $table->string('to_time')->nullable();
            $table->text('job_description')->nullable();
            $table->text('required_skills')->nullable();
            $table->string('status')->default(1);
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
        Schema::dropIfExists('employee_requisitions');
    }
};
