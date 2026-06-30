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
        Schema::table('route_management', function (Blueprint $table) {
            $table->renameColumn('time', 'pick_up_time');
            $table->time('drop_off_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('route_management', function (Blueprint $table) {
            $table->dropColumn('drop_off_time');
        });
    }
};
