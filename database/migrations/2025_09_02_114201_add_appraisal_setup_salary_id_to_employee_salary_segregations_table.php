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
        Schema::table('employee_salary_segregations', function (Blueprint $table) {
            $table->foreignId('appraisal_salary_setup_id')->nullable()->constrained('appraisal_salary_setups');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_salary_segregations', function (Blueprint $table) {
            $table->dropForeign(['appraisal_salary_setup_id']);
            $table->dropColumn('appraisal_salary_setup_id');
        });
    }
};
