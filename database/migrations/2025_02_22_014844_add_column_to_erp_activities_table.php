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
        Schema::table('erp_activities', function (Blueprint $table) {
            $table->tinyInteger('opportunity_qualified')->default(0);
            $table->integer('opportunity_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_activities', function (Blueprint $table) {
            $table->dropColumn('opportunity_qualified');
            $table->dropColumn('opportunity_id');
        });
    }
};
