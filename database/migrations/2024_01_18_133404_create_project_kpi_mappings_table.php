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
        Schema::create('project_kpi_mappings', function (Blueprint $table) {
            $table->string('type_of_indicator')->nullable();
            $table->string('indicator_number')->nullable();
            $table->string('indicator_type')->nullable();
            $table->string('measuring_unit')->nullable();
            $table->string('kpi')->nullable();
            $table->string('reporting_level')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_kpi_mappings');
    }
};
