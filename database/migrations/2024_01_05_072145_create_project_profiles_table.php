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
        Schema::create('project_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('project_profiles');
            $table->string('project_name',150)->nullable();
            $table->string('award_id',150)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('phase',50)->nullable();
            $table->string('end_duration',50)->nullable();
            $table->foreignId('status')->nullable()->constrained('type_values');
            $table->string('donor')->nullable();
            $table->string('project_code',50)->nullable();
            $table->string('thematic_area')->nullable();
            $table->foreignId('pdu_focal_person_id')->nullable()->constrained('users');
            $table->foreignId('project_manager_id')->nullable()->constrained('users');
            $table->unsignedDecimal('budget')->nullable();
            $table->string('target_area')->nullable();
            $table->string('project_description')->nullable();

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
        Schema::dropIfExists('project_profiles');
    }
};
