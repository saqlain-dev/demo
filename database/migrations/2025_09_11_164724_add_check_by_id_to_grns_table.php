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
        Schema::table('g_r_n_s', function (Blueprint $table) {
            $table->foreignId('check_by_id')->nullable()->constrained('employees');
            $table->dateTime('check_by_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('g_r_n_s', function (Blueprint $table) {
            $table->dropForeign(['check_by_id']);
            $table->dropColumn('check_by_id');
            $table->dropColumn('check_by_date');
        });
    }
};
