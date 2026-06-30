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
            $table->string('Department')->nullable();
            $table->string('Designation')->nullable();
            $table->string('Grade')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_payroll_details', function (Blueprint $table) {
            $table->dropColumn('Department');
            $table->dropColumn('Designation');
            $table->dropColumn('Grade');
        });
    }
};
