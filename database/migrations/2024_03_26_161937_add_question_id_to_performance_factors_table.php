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
        Schema::table('performance_factors', function (Blueprint $table) {
            $table->dropColumn('performance_factor_type');
            $table->dropForeign(['performance_factor_value']);
            $table->dropColumn('performance_factor_value');

            $table->foreignId('question_id')->nullable()->constrained('section_questions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('performance_factors', function (Blueprint $table) {
            //
        });
    }
};
