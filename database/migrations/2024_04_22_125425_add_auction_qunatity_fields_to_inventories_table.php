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
            $table->integer('auction_quantity')->default(0);
            $table->integer('donate_quantity')->default(0);
            $table->integer('dispose_quantity')->default(0);
            $table->integer('idle_items')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn('auction_quantity');
            $table->dropColumn('donate_quantity');
            $table->dropColumn('dispose_quantity');
            $table->dropColumn('idle_items');
        });
    }
};
