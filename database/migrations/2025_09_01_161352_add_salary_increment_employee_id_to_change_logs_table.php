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
        Schema::table('employee_change_logs', function (Blueprint $table) {
            $table->foreignId('salary_increments_id')->nullable()->constrained('salary_increments');
            $table->foreignId('salary_increment_employees_id')->nullable()->constrained('salary_increment_employees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_change_logs', function (Blueprint $table) {
            $table->dropForeign(['salary_increments_id']);
            $table->dropColumn('salary_increments_id');
            $table->dropForeign(['salary_increment_employees_id']);
            $table->dropColumn('salary_increment_employees_id');
        });
    }
};
