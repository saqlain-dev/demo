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
        Schema::create('employee_ledgers', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('EmployeeLedgerNumber')->nullable();
            $table->foreignId('EmployeeId')->nullable()->constrained('employees');
            $table->foreignId('ReferenceId')->nullable()->constrained('advance_salary_installments');
            $table->string('VoucherType')->nullable();
            $table->date('TransactionDate')->nullable();
            $table->decimal('Debit',18,2)->nullable();
            $table->decimal('Credit',18,2)->nullable();
            $table->text('Description',18,2)->nullable();
            $table->date('PayMonth')->nullable();
            $table->foreignId('PayrollDetailId')->nullable()->constrained('employee_payroll_details');
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
        Schema::dropIfExists('employee_ledgers');
    }
};
