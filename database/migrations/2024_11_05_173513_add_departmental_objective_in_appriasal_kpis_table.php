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
        Schema::table('appriasal_kpis', function (Blueprint $table) {
            $table->foreignId('departmental_objective_id')->nullable()->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appriasal_kpis', function (Blueprint $table) {
            $table->dropForeign(['departmental_objective_id']);
            $table->dropColumn('departmental_objective_id');
        });
    }
};
