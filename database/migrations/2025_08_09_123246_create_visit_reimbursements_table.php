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
        Schema::create('visit_reimbursements', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('pr_id')->nullable()->constrained('purchase_requests');
            $table->string('visit_by')->nullable();
            $table->foreignId('atr_id')->nullable()->constrained('air_travel_requests');
            $table->foreignId('vr_id')->nullable()->constrained('vehicle_requests');
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
        Schema::dropIfExists('visit_reimbursements');
    }
};
