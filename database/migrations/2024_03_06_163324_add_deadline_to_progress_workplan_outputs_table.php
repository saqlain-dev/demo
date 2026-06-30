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
        Schema::table('progress_workplan_outputs', function (Blueprint $table) {
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('output_quarterly_target')->nullable()->change();
            $table->renameColumn('output_quarterly_target', 'target');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progress_workplan_outputs', function (Blueprint $table) {
            $table->dropColumn('start_date','end_date');
            $table->renameColumn('target', 'output_quarterly_target');

        });
    }
};
