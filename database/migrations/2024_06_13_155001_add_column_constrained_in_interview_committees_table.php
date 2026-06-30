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
        Schema::table('interview_committees', function (Blueprint $table) {
            $table->dropForeign(['apply_job_id']);
            $table->dropColumn('apply_job_id');
        });
        Schema::table('interview_committees', function (Blueprint $table) {
            $table->foreignId('apply_job_id')->nullable()->constrained('manage_jobs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interview_committees', function (Blueprint $table) {
            $table->dropForeign(['apply_job_id']);
            $table->dropColumn('apply_job_id');
        });
        Schema::table('interview_committees', function (Blueprint $table) {
            $table->foreignId('apply_job_id')->nullable()->constrained('apply_jobs');
        });
    }
};
