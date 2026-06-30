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
        Schema::table('project_rrf_goal_indicators', function (Blueprint $table) {
            $table->integer('las_rrf_goal_indicator_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_rrf_goal_indicators', function (Blueprint $table) {
            $table->dropColumn('las_rrf_goal_indicator_id');
        });
    }
};
