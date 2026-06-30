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
        Schema::table('salary_ranges', function (Blueprint $table) {
            $table->renameColumn('salary_range', 'min_range');
            $table->double('max_range',16,2)->nullable();
        });
        Schema::table('salary_ranges', function (Blueprint $table) {

            $table->integer('min_range',16,2)->change()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_ranges', function (Blueprint $table) {
            $table->dropColumn('min_range');
            $table->dropColumn('max_range');
        });
    }
};
