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
        Schema::table('appraisal_employee_allowances', function (Blueprint $table) {
             $table->foreignId('salary_increment_employees_id')->nullable()->constrained('salary_increment_employees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appraisal_employee_allowances', function (Blueprint $table) {
            $table->dropForeign(['salary_increment_employees_id']);
            $table->dropColumn('salary_increment_employees_id');
        });
    }
};
