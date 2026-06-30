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
            if (!Schema::hasColumn('mne_plan_details', 'unit_of_measure')) {
				$table->string('unit_of_measure')->nullable();
			}
			
			if (!Schema::hasColumn('mne_plan_details', 'expected_goal')) {
				$table->text('expected_goal')->nullable();
			}
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
