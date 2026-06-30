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
        Schema::table('advance_salaries', function (Blueprint $table) {
            $table->integer('is_voucher_posted')->default(0);
            $table->integer('voucher_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advance_salaries', function (Blueprint $table) {
            $table->dropColumn('is_voucher_posted');
            $table->dropColumn('voucher_id');
        });
    }
};
