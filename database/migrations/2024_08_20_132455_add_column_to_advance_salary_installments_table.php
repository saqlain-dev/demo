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
        Schema::table('advance_salary_installments', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->constrained();
            $table->unsignedInteger('PayrollDetailId')->nullable();
            $table->date('paidDate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advance_salary_installments', function (Blueprint $table) {
            //
        });
    }
};
