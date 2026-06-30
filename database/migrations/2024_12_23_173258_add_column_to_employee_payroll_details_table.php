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
            $table->date('ContractStartDate')->nullable();
            $table->date('ContractEndDate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_payroll_details', function (Blueprint $table) {
            $table->dropColumn('ContractStartDate');
            $table->dropColumn('ContractEndDate');
        });
    }
};
