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
            $table->decimal('total_marks',10)->nullable();
            $table->decimal('obtained_marks',10)->nullable();
            $table->decimal('percentage_obtained',5)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('performance_plannings', function (Blueprint $table) {
            $table->dropColumn(['obtained_marks','total_marks','percentage_obtained']);
        });
    }
};
