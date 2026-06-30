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
        Schema::table('inventories', function (Blueprint $table) {
            $table->unsignedTinyInteger('inventory_type')->nullable();
            $table->foreignId('po_detail_id')->nullable()->constrained('purchase_order_details');
            $table->foreignId('location_id')->nullable()->constrained();
            $table->string('gl_code')->nullable();
            $table->text('description')->nullable();
            $table->string('serial_no')->nullable();
            $table->string('inventory_no')->nullable();
            $table->date('physical_verification_date')->nullable();
            $table->text('remarks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            //
        });
    }
};
