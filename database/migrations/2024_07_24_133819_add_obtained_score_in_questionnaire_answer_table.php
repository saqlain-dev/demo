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
        Schema::table('questionnaire_answers', function (Blueprint $table) {
            $table->decimal('obtained_score', 10, 2)->default(0);
            $table->decimal('obtained_percentage', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questionnaire_answers', function (Blueprint $table) {
            $table->dropColumn('obtained_score');
            $table->dropColumn('obtained_percentage');
        });
    }
};
