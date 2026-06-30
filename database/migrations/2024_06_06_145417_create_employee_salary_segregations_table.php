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
        Schema::create('employee_salary_segregations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emp_salary_setup_id')->nullable()->constrained('employee_salary_setups');
            $table->foreignId('project_id')->nullable()->constrained('project_profiles');
            $table->float('salary_percentage')->nullable();
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('employee_salary_segregations');
    }
};
