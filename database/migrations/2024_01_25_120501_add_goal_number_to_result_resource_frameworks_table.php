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
        Schema::table('result_resource_frameworks', function (Blueprint $table) {
            $table->string('goal_number',200)->nullable();
            $table->string('las_sp_statement')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('result_resource_frameworks', function (Blueprint $table) {
            $table->dropColumn('goal_number');
            $table->dropColumn('las_sp_statement');
        });
    }
};
