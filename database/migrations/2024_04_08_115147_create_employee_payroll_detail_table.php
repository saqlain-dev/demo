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
        Schema::create('employee_payroll_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('PayrollMasterId')->nullable()->constrained('employee_payroll_masters');
            $table->foreignId('EmployeeId')->nullable()->constrained('employees');
            $table->integer('IsBonusSalary')->nullable();
            $table->date('PaymentDate')->nullable();
            $table->string('CalculateBy', 10);
            $table->date('PayMonth')->nullable();
            $table->date('PayPeriodFrom')->nullable();
            $table->date('PayPeriodTo')->nullable();
            $table->integer('TotalWorkingDays')->nullable();
            $table->decimal('MonthlySalary', 19, 4)->nullable();
            $table->decimal('NetSalary', 19, 4)->nullable();
            $table->decimal('TotalDeductions', 19, 4)->nullable();
            $table->decimal('TotalAllowances', 19, 4)->nullable();
            $table->decimal('NetPay', 19, 4);
            $table->string('ModeOfPayment', 20)->nullable();
            $table->string('ChequeNo', 50)->nullable();
            $table->integer('IsActive')->default(1);
            $table->integer('IsDelete')->default(0);
            $table->date('DateCreated');
            $table->date('DateModified')->nullable();
            $table->decimal('DailySalary', 19, 4)->nullable();
            $table->float('Absences')->nullable();
            $table->float('EncashedLeaves')->nullable();
            $table->decimal('AbsencesAmount', 19, 4)->nullable();
            $table->decimal('EncashedAmount', 19, 4)->nullable();
            $table->string('GeneratedVia', 20)->nullable();
            $table->decimal('GrossSalary', 19, 4)->nullable();
            $table->string('BankAccountNo', 50)->nullable();
            $table->integer('SalaryAccID')->nullable();
            $table->integer('AdvanceAccID')->nullable();
            $table->integer('BankID')->nullable();
            $table->string('BonusType', 200)->nullable();
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
        Schema::dropIfExists('employee_payroll_detail');
    }
};
