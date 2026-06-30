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
        Schema::table('shift_details', function (Blueprint $table) {
            $table->unsignedInteger('isDayOff')->default(0);
            $table->time('shift_start_time')->change();
            $table->time('shift_end_time')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_details', function (Blueprint $table) {
            $table->dropColumn('isDayOff');
        });
    }
};
