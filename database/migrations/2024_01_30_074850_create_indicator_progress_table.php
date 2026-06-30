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
        Schema::create('indicator_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('progress_workplan_id')->nullable()->constrained('progress_workplans');
            $table->string('type_of_indicator')->nullable();
            $table->integer('type_id')->nullable();
            $table->integer('indicator_id')->nullable();
            $table->string('progress_status')->nullable();
            $table->string('kpi')->nullable();
            $table->string('reporting_level')->nullable();
            $table->foreignId('form_id')->nullable()->constrained('questionnaire_forms');
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
        Schema::dropIfExists('indicator_progress');
    }
};
