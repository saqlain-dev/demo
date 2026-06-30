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
        Schema::create('progress_workplans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('project_profiles');
            $table->string('project_workplan',100)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('workplan_activities', function (Blueprint $table) {
            $table->dropColumn('progress_workplan_id');
            //$table->foreignId('progress_workplan_id')->nullable()->constrained('progress_workplans');
        });

        Schema::table('workplan_activities', function (Blueprint $table) {
            //$table->dropColumn('progress_workplan_id');
            $table->foreignId('progress_workplan_id')->nullable()->constrained('progress_workplans');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress_workplans');
    }
};
