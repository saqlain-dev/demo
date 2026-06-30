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
        Schema::create('employee_cola_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('EmployeeId')->nullable()->constrained('employees');
            $table->decimal('salary_before_cola',18,2)->nullable();
            $table->decimal('salary_after_cola',18,2)->nullable();
            $table->foreignId('salary_range_id')->nullable()->constrained('salary_ranges');
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
        Schema::dropIfExists('employee_cola_histories');
    }
};
