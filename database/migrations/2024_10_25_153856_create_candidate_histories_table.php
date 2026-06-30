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
        Schema::create('candidate_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apply_job_id')->nullable()->constrained('apply_jobs');
            $table->integer('old_status')->nullable();
            $table->integer('new_status')->nullable();
            $table->date('date');
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
        Schema::dropIfExists('candidate_histories');
    }
};
