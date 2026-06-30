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
        Schema::table('project_budget_details', function (Blueprint $table) {
            $table->foreignId('unit_type')->nullable()->constrained('type_values');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_budget_details', function (Blueprint $table) {
            $table->dropForeign(['unit_type']);
            $table->dropColumn('unit_type');
        });
    }
};
