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
        Schema::create('employee_timesheet_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_time_sheet_id')->nullable()->constrained('employee_timesheets');
            $table->foreignId('project_id')->nullable()->constrained('project_profiles');
            $table->date('timesheet_date')->nullable();
            $table->unsignedInteger('employee_work_percent')->nullable();
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
        Schema::dropIfExists('employee_timesheet_details');
    }
};
