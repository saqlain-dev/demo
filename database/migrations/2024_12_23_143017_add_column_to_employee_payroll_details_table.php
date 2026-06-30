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
        Schema::table('employee_payroll_details', function (Blueprint $table) {
            $table->integer('AbsentWOLeave')->nullable();
            $table->decimal('AbsentDaysAmount',18,2)->nullable();
            $table->integer('LateEarlyExit')->nullable();
            $table->decimal('LateEarlyExitDeduction',18,2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_payroll_details', function (Blueprint $table) {
            $table->dropColumn('AbsentDaysAmount');
            $table->dropColumn('LateEarlyExitDeduction');
            $table->dropColumn('AbsentWOLeave');
            $table->dropColumn('LateEarlyExit');
        });
    }
};
