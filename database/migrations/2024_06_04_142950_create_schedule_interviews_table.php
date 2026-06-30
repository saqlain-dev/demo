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
        Schema::create('schedule_interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apply_job_id')->nullable()->constrained('apply_jobs');
            $table->date('interview_date')->nullable();
            $table->time('interview_time')->nullable();
            $table->text('link')->nullable();
            $table->integer('interview_mode')->nullable();
            $table->text('remarks')->nullable();
            $table->integer('status')->default(1);
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
        Schema::dropIfExists('schedule_interviews');
    }
};
