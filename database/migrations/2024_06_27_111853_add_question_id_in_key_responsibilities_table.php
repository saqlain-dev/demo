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
        Schema::table('key_responsibilities', function (Blueprint $table) {
            $table->foreignId('question_id')->nullable()->constrained('section_questions');
            $table->foreignId('section_id')->nullable()->constrained('appriasal_kpis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('key_responsibilities', function (Blueprint $table) {
            //
        });
    }
};
