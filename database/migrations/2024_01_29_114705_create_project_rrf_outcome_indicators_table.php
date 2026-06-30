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
        Schema::create('project_rrf_outcome_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proj_rrf_outcome_id')->nullable()->constrained('project_rrf_outcomes');
            $table->integer('sp_id')->nullable();
            $table->integer('las_rrf_outcome_id')->nullable();
            $table->integer('sp_indicator_id')->nullable();
            $table->string('outcome_indicator_number')->nullable();
            $table->string('outcome_indicator_statement')->nullable();
            $table->string('baseline')->nullable();
            $table->string('lop_target')->nullable();
            $table->string('yearly_target')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('project_rrf_outcomes', function (Blueprint $table) {
            //$table->dropForeign('project_id');
            $table->dropColumn(['outcome_indicator_number','outcome_baseline','outcome_lop_target', 'yearly_target', 'outcome_indicator_statement', 'las_sp_indicator']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_rrf_outcome_indicators');
    }
};
