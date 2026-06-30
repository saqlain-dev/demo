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
        Schema::create('ticket_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_plan_id')->nullable()->constrained('audit_plans');
            $table->foreignId('employee_id')->nullable()->constrained('employees');
            $table->date('deadline_date')->nullable();
            $table->text('scope')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('ticket_status_id')->nullable()->constrained('type_values');
            
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
        Schema::dropIfExists('ticket_schedules');
    }
};
