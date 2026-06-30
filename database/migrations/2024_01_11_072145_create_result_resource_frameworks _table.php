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
        Schema::create('result_resource_frameworks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('project_profiles');
            $table->string('project_goal')->nullable();
            $table->string('goal_statement')->nullable();
            $table->string('goal_indicator')->nullable();
            $table->string('goal_baseline')->nullable();
            $table->string('goal_lop_target')->nullable();
            $table->string('yearly_target')->nullable();
            $table->string('goal_indicator_statement')->nullable();
            $table->string('las_rf_statement')->nullable();
            $table->string('las_rf_indicator')->nullable();

            $table->string('las_sp_statement')->nullable();
            $table->string('las_sp_indicator')->nullable();

            $table->string('las_rrf_indicator')->nullable();

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
