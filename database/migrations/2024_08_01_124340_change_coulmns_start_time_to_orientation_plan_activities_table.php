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
        Schema::table('orientation_plan_activities', function (Blueprint $table) {
            $table->renameColumn('start_time', 'activity_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orientation_plan_activities', function (Blueprint $table) {
            $table->renameColumn('activity_date','start_time');
        });
    }
};
