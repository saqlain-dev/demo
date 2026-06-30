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
        Schema::table('purchase_request_rfqs', function (Blueprint $table) {
            $table->unsignedTinyInteger('is_comp_generated')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_request_rfqs', function (Blueprint $table) {
            $table->dropColumn('is_comp_generated');
        });
    }
};
