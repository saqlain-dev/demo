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
            $table->foreignId('employee_workplan_id')->nullable()->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('performance_plannings', function (Blueprint $table) {
            $table->dropForeign(['employee_workplan_id']);
            $table->dropColumn(['employee_workplan_id']);
        });
    }
};
