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
        Schema::table('las_invoices', function (Blueprint $table) {
            $table->foreignId('nofo_id')->nullable()->constrained('nofos');
            $table->foreignId('sub_grant_id')->nullable()->constrained('sub_grants');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('las_invoices', function (Blueprint $table) {
            $table->dropForeign(['nofo_id']);
            $table->dropColumn('nofo_id');
            $table->dropForeign(['sub_grant_id']);
            $table->dropColumn('sub_grant_id');
        });
    }
};
