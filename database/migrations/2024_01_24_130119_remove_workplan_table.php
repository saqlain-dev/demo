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

        //if (Schema::hasTable('progress_workplans')) {
            Schema::table('workplan_activities', function (Blueprint $table) {
                $table->dropForeign(['progress_workplan_id']);
            });
        //}

        // Drop the 'progress_workplans' table
        Schema::dropIfExists('progress_workplans');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
