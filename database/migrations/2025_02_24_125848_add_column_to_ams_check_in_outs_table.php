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
            $table->unsignedInteger('Is_WFH')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ams_check_in_outs', function (Blueprint $table) {
            $table->dropColumn('Is_WFH');
        });
    }
};
