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
        Schema::create('workplan_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('progress_workplan_id')->nullable()->constrained('progress_workplans');
            $table->foreignId('project_id')->nullable()->constrained('project_profiles');
            $table->integer('activity_type')->nullable()->comment('1 for Goal, 2 for outcome, 3 for output');
            $table->text('activity')->nullable();
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
        Schema::dropIfExists('workplan_activities');
    }
};
