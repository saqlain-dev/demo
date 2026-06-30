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
        Schema::create('appraisal_salary_setups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_increment_employees_id')->nullable()->constrained('salary_increment_employees');
            $table->foreignId('employee_id')->nullable()->constrained('employees');
            $table->decimal('basic_salary',18,2)->default(0);
            $table->decimal('monthly_salary',18,2)->default(0);
            $table->decimal('dailyWage',18,2)->default(0);
            $table->decimal('overTimeRate',18,2)->default(0);
            $table->string('bankAccountNumber')->nullable();
            $table->foreignId('bankId')->nullable()->constrained('type_values');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->integer('status')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appraisal_salary_setups');
    }
};
