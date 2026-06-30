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
        Schema::table('erp_activity_attachments', function (Blueprint $table) {
            $table->tinyInteger('support_required')->default(0);
            $table->text('next_meeting_remarks')->nullable();
            $table->tinyInteger('opportunity_qualified')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_activity_attachments', function (Blueprint $table) {
            $table->dropColumn('support_required');
            $table->dropColumn('next_meeting_remarks');
            $table->dropColumn('opportunity_qualified');
        });
    }
};
