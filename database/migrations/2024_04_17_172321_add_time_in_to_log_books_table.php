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
        Schema::table('log_books', function (Blueprint $table) {
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->string('distance')->nullable();
            $table->string('pool_type')->nullable();
            $table->text('other_detail')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_books', function (Blueprint $table) {
            $table->dropColumn('time_in');
            $table->dropColumn('time_out');
            $table->dropColumn('distance');
            $table->dropColumn('pool_type');
            $table->dropColumn('other_detail');
        });
    }
};
