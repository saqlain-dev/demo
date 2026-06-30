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
            $table->tinyInteger('IsApproved')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_general_ledgers', function (Blueprint $table) {
            $table->dropColumn('IsApproved');
        });
    }
};
