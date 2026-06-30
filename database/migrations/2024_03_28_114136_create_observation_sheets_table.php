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
        Schema::create('observation_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mne_plan_id')->nullable()->constrained('project_mne_plans');
            $table->date('date')->nullable();
            $table->integer('type_of_activity')->nullable();
            $table->integer('mne_officer_id')->nullable();
            $table->integer('district_id')->nullable();
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
        Schema::dropIfExists('observation_sheets');
    }
};
