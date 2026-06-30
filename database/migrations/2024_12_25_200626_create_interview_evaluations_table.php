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
        Schema::create('interview_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manage_job_id')->nullable()->constrained('manage_jobs');
            $table->foreignId('apply_job_id')->nullable()->constrained('apply_jobs');
            $table->foreignId('employee_id')->nullable()->constrained('employees');
            $table->integer('interview_id')->nullable();
            $table->boolean('recommendation')->nullable();
            $table->text('feedback')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_results');
    }
};
