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
        Schema::table('special_holidays', function (Blueprint $table) {
            $table->unsignedInteger('religion')->nullable()->default(0);
            $table->unsignedInteger('branch_office')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('special_holidays', function (Blueprint $table) {
            $table->dropColumn('religion');
            $table->dropColumn('branch_office');
        });
    }
};
