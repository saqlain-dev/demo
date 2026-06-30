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
        Schema::table('ams_check_in_outs', function (Blueprint $table) {
            $table->unsignedInteger('IsUpdated')->default(0);
            $table->text('attendance_remarks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ams_check_in_outs', function (Blueprint $table) {
            $table->dropColumn('IsUpdated');
            $table->dropColumn('attendance_remarks');
        });
    }
};
