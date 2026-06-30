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
        Schema::create('manage_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_title')->nullable();
            $table->integer('department_id')->nullable();
            $table->string('job_location')->nullable();
            $table->integer('no_of_vacancies')->nullable();
            $table->string('experience')->nullable();
            $table->string('salary')->nullable();
            $table->foreignId('required_job_type')->nullable()->constrained('type_values');
            $table->integer('status')->default(1);
            $table->text('responsibilities')->nullable();
            $table->date('deadline')->nullable();
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
        Schema::dropIfExists('manage_jobs');
    }
};
