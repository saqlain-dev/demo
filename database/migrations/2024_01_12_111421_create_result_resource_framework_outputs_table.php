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
        Schema::create('result_resource_framework_outputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('project_profiles');
            $table->string('rrf_output_number',100)->nullable();
            $table->string('rrf_output_statement')->nullable();
            $table->string('rrf_output_indicator')->nullable();
            $table->string('indicator_baseline')->nullable();
            $table->string('lop_target')->nullable();
            $table->string('yearly_target')->nullable();
            $table->string('indicator_statement')->nullable();
            $table->string('sp_statement')->nullable();
            $table->string('sp_indicator_no')->nullable();
//            $table->string('las_sp_statement')->nullable();
//            $table->string('las_sp_indicator')->nullable();
            $table->string('rrf_indicator')->nullable();

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
        Schema::dropIfExists('result_resource_framework_outputs');
    }
};
