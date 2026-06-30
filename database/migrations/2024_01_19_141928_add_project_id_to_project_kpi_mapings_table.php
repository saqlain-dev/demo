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
        Schema::table('project_kpi_mappings', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->constrained('project_profiles');
            $table->string('selected_type_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_kpi_mapings', function (Blueprint $table) {
            //
        });
    }
};
