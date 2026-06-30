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
        Schema::create('las_rrf_output_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rrf_output_id')->nullable()->constrained('result_resource_framework_outputs');
            $table->integer('sp_id')->nullable();
            $table->integer('sp_indicator_id')->nullable();
            $table->string('output_indicator_number')->nullable();
            $table->string('baseline')->nullable();
            $table->string('lop_target')->nullable();
            $table->string('yearly_target')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });

//        Schema::table('result_resource_framework_outputs', function (Blueprint $table) {
//            //$table->dropForeign('project_id');
//            $table->dropColumn(['rrf_output_indicator','indicator_baseline', 'lop_target', 'yearly_target', 'indicator_statement', 'sp_indicator_no', 'rrf_indicator']);
//        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('las_rrf_output_indicators');
    }
};
