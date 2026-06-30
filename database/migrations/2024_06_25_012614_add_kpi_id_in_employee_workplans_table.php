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
        Schema::table('employee_workplans', function (Blueprint $table) {
            $table->foreignId('appriasal_kpi_id')->nullable()->constrained('appriasal_kpis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_workplans', function (Blueprint $table) {
            //
        });
    }
};
