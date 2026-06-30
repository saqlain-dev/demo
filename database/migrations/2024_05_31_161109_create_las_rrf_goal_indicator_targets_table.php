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
        Schema::create('las_rrf_goal_indicator_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('las_rrf_goal_indicator_id')->constrained();
            $table->string('year')->nullable();
            $table->string('target')->nullable();
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
        Schema::dropIfExists('las_rrf_goal_indicator_targets');
    }
};
