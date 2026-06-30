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
        Schema::table('item_variants', function (Blueprint $table) {
            $table->string('barcode')->nullable();
            $table->string('vendor')->nullable();
            $table->decimal('cost',18,2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_variants', function (Blueprint $table) {
            $table->dropColumn('barcode');
            $table->dropColumn('vendor');
            $table->dropColumn('cost');
        });
    }
};
