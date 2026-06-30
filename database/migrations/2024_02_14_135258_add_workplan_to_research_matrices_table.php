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
        Schema::table('research_matrices', function (Blueprint $table) {
            $table->foreignId('progress_workplan_id')->nullable()->constrained('progress_workplans');
            $table->integer('type')->nullable();
            $table->integer('type_id')->nullable();
            $table->integer('type_category_id')->nullable();
            $table->text('research_objective')->nullable();
            $table->integer('methodology_id')->nullable();
            $table->integer('research_component_place_id')->nullable();
            $table->string('allocated_budget')->nullable();
            $table->integer('focal_person')->nullable();
            $table->integer('responsible')->nullable();
            $table->integer('accountable')->nullable();
            $table->integer('consulted')->nullable();
            $table->integer('informed')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('research_matrices', function (Blueprint $table) {
            //
        });
    }
};
