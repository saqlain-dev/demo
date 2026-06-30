<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->decimal('year_in_business')->nullable()->change();
        });

        Schema::table('las_invoice_details', function (Blueprint $table) {
            $table->decimal('total_amount', 13, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->unsignedTinyInteger('year_in_business')->nullable()->change();
        });

        Schema::table('las_invoice_details', function (Blueprint $table) {
            $table->dropColumn('total_amount');
        });
    }
};
