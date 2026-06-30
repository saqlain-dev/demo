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
        Schema::table('vendor_atr_quotations', function (Blueprint $table) {
            $table->string('check_in_time')->nullable();
            $table->string('flight_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_atr_quotations', function (Blueprint $table) {
            $table->dropColumn('check_in_time');
            $table->dropColumn('flight_name');
        });
    }
};
