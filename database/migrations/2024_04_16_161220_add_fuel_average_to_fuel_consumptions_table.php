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
        Schema::table('fuel_consumptions', function (Blueprint $table) {
            $table->string('fuel_average')->nullable();
            $table->date('date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_consumptions', function (Blueprint $table) {
            $table->dropColumn('fuel_average');
            $table->dropColumn('date');
        });
    }
};
