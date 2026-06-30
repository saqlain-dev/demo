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
        Schema::table('complaint_committees', function (Blueprint $table) {
            $table->foreignId('complaint_meeting_id')->nullable()->constrained('complaint_meetings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaint_committees', function (Blueprint $table) {
            $table->dropForeign(['complaint_meeting_id']);
            $table->dropColumn('complaint_meeting_id');
        });
    }
};
