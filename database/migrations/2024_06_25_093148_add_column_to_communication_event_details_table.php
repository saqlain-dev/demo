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
        Schema::table('communication_event_details', function (Blueprint $table) {
            $table->integer('status')->nullable();
            $table->string('final_attachment')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('communication_event_details', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('final_attachment');
        });
    }
};
