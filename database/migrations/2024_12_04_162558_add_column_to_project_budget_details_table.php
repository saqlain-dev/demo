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
            $table->foreignId('budget_category')->nullable()->constrained('type_values');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_budget_details', function (Blueprint $table) {
            $table->dropForeign(['budget_category']);
            $table->dropColumn('budget_category');
        });
    }
};
