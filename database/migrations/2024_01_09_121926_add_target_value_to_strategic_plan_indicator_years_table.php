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
        Schema::table('strategic_plan_indicator_years', function (Blueprint $table) {
            $table->unsignedDecimal('planned')->nullable()->after('year');
            $table->unsignedDecimal('actual')->nullable()->after('year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('strategic_plan_indicator_years', function (Blueprint $table) {
            //
        });
    }
};
