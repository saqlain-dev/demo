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
            $table->foreignId('inventory_id')->nullable()->constrained('inventories');
            $table->date('purchase_date')->nullable();
            $table->foreignId('po_id')->nullable()->constrained('purchase_orders');
            $table->foreignId('location_id')->nullable()->constrained('locations');
            $table->foreignId('store_id')->nullable()->constrained('locations');
            $table->string('gl_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_variants', function (Blueprint $table) {
            //
        });
    }
};
