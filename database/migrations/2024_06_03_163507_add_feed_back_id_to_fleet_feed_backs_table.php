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
        Schema::table('fleet_feed_backs', function (Blueprint $table) {
            $table->foreignId('feed_back_id')->nullable()->constrained('fleet_feed_backs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fleet_feed_backs', function (Blueprint $table) {
            $table->dropForeign(['feed_back_id']);
            $table->dropColumn('feed_back_id');
        });
    }
};
