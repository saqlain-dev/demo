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
        Schema::table('sales_teams', function (Blueprint $table) {
            $table->foreignId('sales_head_id')->nullable()->constrained('employees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_teams', function (Blueprint $table) {
            $table->dropForeign(['sales_head_id']);
            $table->dropColumn('sales_head_id');
        });
    }
};
