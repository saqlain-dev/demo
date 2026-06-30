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
            $table->text('test_description')->nullable();
            $table->text('test_instruction')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questionnaire_forms', function (Blueprint $table) {
            $table->dropColumn('test_description');
            $table->dropColumn('test_instruction');
        });
    }
};
