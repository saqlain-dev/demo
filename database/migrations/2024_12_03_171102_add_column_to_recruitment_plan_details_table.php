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
        Schema::table('recruitment_plan_details', function (Blueprint $table) {
            $table->unsignedTinyInteger('is_budgeted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_plan_details', function (Blueprint $table) {
            $table->dropColumn('is_budgeted');
        });
    }
};
