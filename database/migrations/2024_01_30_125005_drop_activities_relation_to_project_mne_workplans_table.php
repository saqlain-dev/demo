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
        // Remove the foreign key constraints
        Schema::table('project_mne_workplans', function (Blueprint $table) {
            $table->dropForeign(['activity_id']);
        });

//        // Add a new foreign key constraint referencing the 'activities' table
//        Schema::table('project_mne_workplans', function (Blueprint $table) {
//            $table->foreign('activity_id')
//                ->references('id')
//                ->on('activities')
//                ->onUpdate('cascade');
//        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_mne_workplans', function (Blueprint $table) {
            //
        });
    }
};
