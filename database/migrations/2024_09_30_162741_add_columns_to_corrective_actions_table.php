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
        Schema::table('corrective_actions', function (Blueprint $table) {
            $table->double('depreciation_amount')->nullable(); // Add depreciation_amount column
            $table->double('estimate_amount')->nullable();     // Add estimate_amount column
            $table->double('total_amount')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('corrective_actions', function (Blueprint $table) {
            $table->dropColumn(['depreciation_amount', 'estimate_amount', 'total_amount']);
        });
    }
};
