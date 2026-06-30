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
        Schema::create('project_mne_workplans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('project_profiles');
            $table->year('year')->nullable();
            $table->enum('quarter', ['Q1','Q2','Q3','Q4']);
            $table->string('week')->nullable();
            $table->date('date')->nullable();
            $table->foreignId('district_id')->nullable()->constrained();
            $table->foreignId('activity_id')->nullable()->constrained('type_values');
            $table->string('venue_of_activity')->nullable();
            $table->string('project_focal_person')->nullable();
            $table->string('mne_responsible_person')->nullable();
            $table->text('mne_requirement')->nullable();

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
        Schema::dropIfExists('project_mne_workplans');
    }
};
