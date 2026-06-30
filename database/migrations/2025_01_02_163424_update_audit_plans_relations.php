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
        // Fix Audit plan report relations
        Schema::table('audit_plan_reports', function (Blueprint $table) {
            $table->foreignId('audit_schedule_id')->nullable()->constrained('audit_schedules');
        });
        Schema::table('audit_plan_reports', function (Blueprint $table) {
            $table->dropForeign(['audit_plan_id']);
            $table->dropColumn('audit_plan_id');
        });
        
        // Fix ticket_schedule relations
        Schema::table('ticket_schedules', function (Blueprint $table) {
            $table->foreignId('audit_schedule_id')->nullable()->constrained('audit_schedules');
        });
        Schema::table('ticket_schedules', function (Blueprint $table) {
            $table->dropForeign(['audit_plan_id']);
            $table->dropColumn('audit_plan_id');
        });
        
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_plan_reports', function (Blueprint $table) {
            $table->foreignId('audit_plan_id')->nullable()->constrained('audit_schedules');
        });
        Schema::table('audit_plan_reports', function (Blueprint $table) {
            $table->dropForeign(['audit_schedule_id']);
            $table->dropColumn('audit_schedule_id');
        });

        Schema::table('ticket_schedules', function (Blueprint $table) {
            $table->foreignId('audit_plan_id')->nullable()->constrained('audit_schedules');
        });
        Schema::table('ticket_schedules', function (Blueprint $table) {
            $table->dropForeign(['audit_schedule_id']);
            $table->dropColumn('audit_schedule_id');
        });
        
        
    }
};
