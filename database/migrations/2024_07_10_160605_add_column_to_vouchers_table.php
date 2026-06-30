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
        Schema::table('vouchers', function (Blueprint $table) {
            $table->integer('approval_status')->default(STATUS::DRAFT);
            $table->text('reason')->nullable();
            $table->string('VoucherFrom')->nullable();
            $table->integer('VoucherFromID')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn('approval_status');
            $table->dropColumn('reason');
            $table->dropColumn('VoucherFrom');
            $table->dropColumn('VoucherFromID');
        });
    }
};
