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
        Schema::table('tbl_general_ledger_details', function (Blueprint $table) {
            $table->foreignId('VoucherTypeID')->nullable()->constrained('type_values');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_general_ledger_details', function (Blueprint $table) {
            $table->dropColumn('VoucherTypeID');
        });
    }
};
