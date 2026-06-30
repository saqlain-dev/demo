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
        Schema::create('candidate_online_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_form_id')->constrained('questionnaire_forms');
            $table->foreignId('apply_job_id')->constrained();
            $table->uuid('uuid')->nullable();
            $table->string('test_url')->nullable();
            $table->string('test_duration')->nullable();
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
        Schema::dropIfExists('candidate_online_tests');
    }
};
