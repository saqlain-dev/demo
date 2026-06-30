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
        Schema::table('communication_events', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['category_id']);
            $table->dropForeign(['sub_category_id']);

            $table->dropColumn('department_id');
            $table->dropColumn('category_id');
            $table->dropColumn('sub_category_id');
            $table->dropColumn('size');
            $table->dropColumn('color_scheme');
            $table->dropColumn('quantity');
            $table->dropColumn('budget');
            $table->dropColumn('other_requirements');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('communication_events', function (Blueprint $table) {
            //
        });
    }
};
