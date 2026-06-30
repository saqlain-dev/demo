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
        Schema::create('employee_payroll_pre_gross_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('PayrollDetailId')->nullable()->constrained('employee_payroll_details');
            $table->integer('PreGrossSalaryId')->nullable()->nullable();
            $table->string('PreGrossAllowanceName')->nullable();
            $table->float('PreGrossPercentage')->nullable();
            $table->double('PreGrossSalaryAmount',18,2)->nullable();
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
        Schema::dropIfExists('employee_payroll_pre_gross_salaries');
    }
};
