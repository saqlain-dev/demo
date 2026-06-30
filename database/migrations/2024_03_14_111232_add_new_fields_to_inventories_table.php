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
        Schema::table('inventories', function (Blueprint $table) {
            $table->unsignedTinyInteger('idle_approval_status')->default(4);
            $table->unsignedTinyInteger('idle_action')->nullable()->comment('1 for Auction, 2 for Donate, 3 for Dispose');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn(['idle_action','idle_approval_status']);
        });
    }
};
