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
        Schema::create('employee_payroll_masters', function (Blueprint $table) {
            $table->id();
            $table->date('PaymentDate')->nullable();
            $table->string('CalculateBy', 10)->nullable();
            $table->date('PayMonth')->nullable();
            $table->date('PayPeriodFrom')->nullable();
            $table->date('PayPeriodTo')->nullable();
            $table->decimal('TotalMonthlySalary', 19, 4)->nullable();
            $table->decimal('TotalNetSalary', 19, 4)->nullable();
            $table->decimal('TotalAllowances', 19, 4)->nullable();
            $table->decimal('TotalDeductions', 19, 4)->nullable();
            $table->decimal('TotalNetPay', 19, 4)->nullable();
            $table->string('ModeOfPayment', 20)->nullable();
            $table->boolean('IsActive')->default(true);
            $table->boolean('IsDelete')->default(false);
            $table->date('DateCreated');
            $table->date('DateModified')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_payroll_master');
    }
};
