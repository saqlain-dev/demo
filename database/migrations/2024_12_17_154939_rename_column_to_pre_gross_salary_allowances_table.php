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
        Schema::table('pre_gross_salary_allowances', function (Blueprint $table) {
            $table->renameColumn('allowance_name', 'allowance_type');
        });
        Schema::table('pre_gross_salary_allowances', function (Blueprint $table) {
            $table->integer('allowance_type')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_gross_salary_allowances', function (Blueprint $table) {
            $table->renameColumn('allowance_type', 'allowance_name');
        });
        Schema::table('pre_gross_salary_allowances', function (Blueprint $table) {
            $table->string('allowance_name')->change();
        });
    }
};
