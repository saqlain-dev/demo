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
        Schema::create('salary_allowance_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('PayrollDetailId')->nullable()->constrained('employee_payroll_details');
            $table->foreignId('EmployeeId')->nullable()->constrained('employees');
            $table->string('Description', 200)->nullable();
            $table->integer('Category')->nullable();
            $table->integer('CalculatedBy')->nullable();
            $table->float('Value')->nullable();
            $table->tinyInteger('IsActive')->default(1);
            $table->tinyInteger('IsDelete')->default(0);
            $table->date('DateCreated')->nullable();
            $table->date('DateModified')->nullable();
            $table->integer('EmployerShareCalculatedBy')->nullable();
            $table->float('EmployerShareValue')->nullable();
            $table->tinyInteger('IsTaxable')->nullable();
            $table->text('Remarks')->nullable();
            $table->decimal('PricePerLiter', 18, 2)->nullable();
            $table->integer('AllowedLiters')->nullable();
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
        Schema::dropIfExists('salary_allowance_deductions');
    }
};
