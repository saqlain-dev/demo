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
        Schema::table('issue_stocks', function (Blueprint $table) {
            $table->unsignedTinyInteger('is_transfer_note_generated')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issue_stocks', function (Blueprint $table) {
            $table->dropColumn('is_transfer_note_generated');
        });
    }
};
