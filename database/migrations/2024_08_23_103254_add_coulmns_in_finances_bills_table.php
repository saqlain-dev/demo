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
        Schema::table('finance_bills', function (Blueprint $table) {
            $table->foreignId('budget_estimate_id')->nullable()->constrained('budget_estimates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('finances_bills', function (Blueprint $table) {
            //
        });
    }
};
