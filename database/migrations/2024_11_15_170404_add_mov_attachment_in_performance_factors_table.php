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
        Schema::table('performance_factors', function (Blueprint $table) {
            $table->unsignedBigInteger('scheduled_check_in_id')->nullable();
            $table->unsignedBigInteger('annual_check_in_id')->nullable();
            $table->string('average_points')->nullable();
        });

        Schema::table('scheduled_check_ins', function (Blueprint $table) {
            $table->decimal('total_kpi_marks', 8, 2)->nullable();
            $table->decimal('obtained_kpi_marks', 8, 2)->nullable();
            $table->decimal('kpi_percentage_obtained', 8, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('performance_factors', function (Blueprint $table) {
            $table->dropColumn('scheduled_check_in_id');
            $table->dropColumn('annual_check_in_id');
            $table->dropColumn('average_points');
        });

        Schema::table('scheduled_check_ins', function (Blueprint $table) {
            $table->dropColumn('total_kpi_marks');
            $table->dropColumn('obtained_kpi_marks');
            $table->dropColumn('kpi_percentage_obtained');
        });
    }
};
