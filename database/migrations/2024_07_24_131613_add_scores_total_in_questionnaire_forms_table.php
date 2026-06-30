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
        Schema::table('questionnaire_forms', function (Blueprint $table) {
            $table->decimal('total_score', 12, 2)->default(0);
            $table->integer('total_questions')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questionnaire_forms', function (Blueprint $table) {
            $table->dropColumn('total_score');
            $table->dropColumn('total_questions');
        });
    }
};
