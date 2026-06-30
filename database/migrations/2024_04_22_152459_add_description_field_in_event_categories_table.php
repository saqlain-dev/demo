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
        Schema::table('event_categories', function (Blueprint $table) {
            $table->text('description')->nullable();
        });
        Schema::table('event_sub_categories', function (Blueprint $table) {
            $table->text('description')->nullable();
        });
        Schema::table('communication_event_details', function (Blueprint $table) {
            $table->foreignId('event_id')->nullable()->constrained('communication_events');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_categories', function (Blueprint $table) {
            $table->dropColumn('description');
        });
        Schema::table('event_sub_categories', function (Blueprint $table) {
            $table->dropColumn('description');
        });
        Schema::table('communication_event_details', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->dropColumn('event_id');
        });
    }
};
