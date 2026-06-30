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
        Schema::table('gratuity_calculations', function (Blueprint $table) {
            $table->double('sub_total')->nullable();
            $table->double('total_amount')->nullable();
        });
        Schema::table('gratuity_calculation_details', function (Blueprint $table) {
            $table->string('percentage')->nullable();
            $table->double('sub_total')->nullable();
            $table->double('total_amount')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gratuity_calculations', function (Blueprint $table) {
            $table->dropColumn('sub_total');
            $table->dropColumn('total_amount');
        });

        Schema::table('gratuity_calculation_details', function (Blueprint $table) {
            $table->dropColumn('percentage');
            $table->dropColumn('sub_total');
            $table->dropColumn('total_amount');
        });
    }
};
