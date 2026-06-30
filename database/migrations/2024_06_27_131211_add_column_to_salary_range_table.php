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
            $table->float('cola_percentage')->nullable();
            $table->unsignedTinyInteger('isApplied')->default(0);
            $table->integer('approval_status')->default(STATUS::DRAFT);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_ranges', function (Blueprint $table) {
            $table->dropColumn('approval_status');
            $table->dropColumn('cola_percentage');
            $table->dropColumn('isApplied');
        });
    }
};
