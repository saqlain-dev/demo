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
            $table->decimal('arrears',18,2)->nullable();
            $table->decimal('tax',16,2)->nullable();
            $table->decimal('EOBI',16,2)->nullable();
            $table->decimal('unpaidInstallment',18,2)->nullable();
            $table->decimal('allowance',18,2)->nullable();
            $table->decimal('deduction',18,2)->nullable();
            $table->text('remarks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_payroll_details', function (Blueprint $table) {
            $table->dropColumn('arrears');
            $table->dropColumn('tax');
            $table->dropColumn('EOBI');
            $table->dropColumn('remarks');
            $table->dropColumn('unpaidInstallment');
            $table->dropColumn('allowance');
            $table->dropColumn('deduction');
        });
    }
};
