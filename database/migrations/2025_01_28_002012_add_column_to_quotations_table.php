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
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['opportunity_id']);
            $table->dropForeign(['lead_id']);
            $table->dropColumn('opportunity_id');
            $table->dropColumn('lead_id');
        });
        Schema::table('quotations', function (Blueprint $table) {
            $table->foreignId('rfp_id')->nullable()->constrained('rfps');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['rfp_id']);
            $table->dropColumn('rfp_id');
        });
    }
};
