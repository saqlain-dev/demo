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
        Schema::create('apply_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->nullable()->constrained('manage_jobs');
            $table->string('candidate_name')->nullable();
            $table->string('candidate_cnic')->nullable();
            $table->string('candidate_email')->nullable();
            $table->string('candidate_phone')->nullable();
            $table->string('candidate_gender')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('current_location')->nullable();
            $table->string('currently_employed')->nullable();
            $table->string('currently_salary')->nullable();
            $table->string('expected_salary')->nullable();
            $table->string('current_company')->nullable();
            $table->string('candidate_resume')->nullable();
            $table->integer('status')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apply_job');
    }
};
