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
        Schema::table('mne_plan_details', function (Blueprint $table) {
            $table->string('unit_of_measure')->nullable();
            $table->text('expected_goal')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mne_plan_details', function (Blueprint $table) {
            //
        });
    }
};
