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
        Schema::table('tbl_general_ledgers', function (Blueprint $table) {
            $table->foreignId('voucher_no')->nullable()->constrained('vouchers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_general_ledgers', function (Blueprint $table) {
            $table->dropForeign(['voucher_no']);
            $table->dropColumn('voucher_no');
        });
    }
};
