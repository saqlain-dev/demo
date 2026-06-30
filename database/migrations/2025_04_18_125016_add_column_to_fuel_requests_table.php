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
        Schema::table('fuel_requests', function (Blueprint $table) {
            $table->integer('budget_detail_id')->nullable();
            $table->decimal('amount',18,2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_requests', function (Blueprint $table) {
            $table->dropColumn('budget_detail_id');
            $table->dropColumn('amount');
        });
    }
};
