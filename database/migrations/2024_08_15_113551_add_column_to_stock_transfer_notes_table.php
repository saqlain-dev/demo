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
        Schema::table('stock_transfer_notes', function (Blueprint $table) {
            $table->string('transfer_from')->nullable();
            $table->string('transfer_to')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_transfer_notes', function (Blueprint $table) {
            $table->dropColumn('transfer_from');
            $table->dropColumn('transfer_to');
        });
    }
};
