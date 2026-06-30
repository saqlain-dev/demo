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
        Schema::create('project_rrf_goal_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proj_rrf_goal_id')->nullable()->constrained('project_rrf_goals');
            $table->integer('sp_id')->nullable();
            $table->integer('las_rrf_goal_id')->nullable();
            $table->integer('sp_indicator_id')->nullable();
            $table->string('goal_indicator_number')->nullable();
            $table->string('goal_indicator_statement')->nullable();
            $table->string('baseline')->nullable();
            $table->string('lop_target')->nullable();
            $table->string('yearly_target')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('project_rrf_goals', function (Blueprint $table) {
            //$table->dropForeign('project_id');
            $table->dropColumn(['goal_indicator_number','goal_baseline','goal_lop_target', 'yearly_target', 'goal_indicator_statement', 'las_sp_indicator']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_rrf_goal_indicators');
    }
};
