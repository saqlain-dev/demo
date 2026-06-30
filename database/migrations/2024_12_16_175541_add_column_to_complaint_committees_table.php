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
        Schema::table('complaint_committees', function (Blueprint $table) {
            $table->foreignId('nda_letter')->nullable()->constrained('generated_letters');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaint_committees', function (Blueprint $table) {
            $table->dropForeign('nda_letter');
            $table->dropColumn('nda_letter');
        });
    }
};
