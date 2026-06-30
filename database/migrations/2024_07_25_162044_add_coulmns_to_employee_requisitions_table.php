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
        Schema::table('employee_requisitions', function (Blueprint $table) {
            $table->integer('salary')->nullable();
            $table->integer('no_of_vacancies')->nullable();
            $table->string('job_location')->nullable();
            $table->integer('job_mode')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_requisitions', function (Blueprint $table) {
            $table->dropColumn('salary');
            $table->dropColumn('no_of_vacancies');
            $table->dropColumn('job_location');
            $table->dropColumn('job_mode');
        });
    }
};
