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
        Schema::table('orientation_plan_activities', function (Blueprint $table) {
            $table->text('activity_date')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orientation_plan_activities', function (Blueprint $table) {
            $table->date('activity_date')->change();
        });
    }
};
