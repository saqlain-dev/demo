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
        Schema::table('work_orders', function (Blueprint $table) {
            $table->boolean('vendor_acknowledged')->default(false);
        });
            Schema::table('purchase_orders', function (Blueprint $table) {
            $table->boolean('vendor_acknowledged')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn('vendor_acknowledged');
        });
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('vendor_acknowledged');
        });
    }
};
