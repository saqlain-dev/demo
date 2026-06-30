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
            $table->unsignedTinyInteger('status')->default(STATUS::DRAFT)->change();
            $table->text('rej_comments')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('g_r_n_s', function (Blueprint $table) {
            //
        });
    }
};
