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
        Schema::table('board_meeting_applicants', function (Blueprint $table) {
            $table->unsignedTinyInteger('IsBoardMember')->default(0);
            $table->unsignedTinyInteger('status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('board_meeting_applicants', function (Blueprint $table) {
            $table->dropColumn('IsBoardMember');
            $table->dropColumn('status');
        });
    }
};
