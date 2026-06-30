<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('project_mne_workplans')->update(['activity_id' => null]);

        Schema::table('project_mne_workplans', function (Blueprint $table) {
            $table->foreign('activity_id')
                ->references('id')
                ->on('activities')
                ->onUpdate('cascade');
        });
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
