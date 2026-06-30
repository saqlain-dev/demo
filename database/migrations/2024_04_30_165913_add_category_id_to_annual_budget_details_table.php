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
        Schema::table('annual_budget_details', function (Blueprint $table) {
            $table->integer('category_id')->nullable();
            $table->integer('sub_category_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('annual_budget_details', function (Blueprint $table) {
            $table->dropColumn('category_id');
            $table->dropColumn('sub_category_id');
        });
    }
};
