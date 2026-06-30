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
        Schema::table('employee_workplan_section_question', function (Blueprint $table) {
            $table->foreignId('employee_workplan_activity_id')->nullable()->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_workplan_section_question', function (Blueprint $table) {
            $table->dropForeign(['employee_workplan_activity_id']);
            $table->dropColumn(['employee_workplan_activity_id']);
        });
    }
};
