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
        Schema::table('performance_plannings', function (Blueprint $table) {
            $table->decimal('total_kpi_marks',10)->nullable();
            $table->decimal('obtained_kpi_marks',10)->nullable();
            $table->decimal('kpi_percentage_obtained',5)->nullable();
            $table->text('employee_comments')->nullable();
            $table->text('supervisor_comments')->nullable();
            $table->text('ceo_comments')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('performance_plannings', function (Blueprint $table) {
            //
        });
    }
};
