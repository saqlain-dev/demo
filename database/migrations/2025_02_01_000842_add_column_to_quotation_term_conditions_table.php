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

        Schema::table('quotation_term_conditions', function (Blueprint $table) {
            $table->dropForeign(['letter_id']);
            $table->dropColumn('letter_id');
        });
        Schema::table('quotation_term_conditions', function (Blueprint $table) {
            $table->foreignId('letter_id')->nullable()->constrained('general_templates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotation_term_conditions', function (Blueprint $table) {
            //
        });
    }
};
