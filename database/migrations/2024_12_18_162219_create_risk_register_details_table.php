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
        Schema::create('risk_register_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risk_register_id')->nullable()->constrained();
            $table->foreignId('employee_id')->nullable()->constrained();
            $table->text('description')->nullable();
            $table->text('control_procedures')->nullable();
            $table->text('risk_closure_reason')->nullable();
            
            $table->foreignId('risk_category_id')->nullable()->constrained('type_values');
            $table->foreignId('risk_probability_id')->nullable()->constrained('type_values');
            $table->foreignId('risk_impact_id')->nullable()->constrained('type_values');
            $table->foreignId('overall_risk_id')->nullable()->constrained('type_values');
            $table->foreignId('risk_approach_id')->nullable()->constrained('type_values');
            $table->foreignId('risk_status_id')->nullable()->constrained('type_values');
            
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
        Schema::dropIfExists('risk_registers');
    }
};
