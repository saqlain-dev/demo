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
        Schema::create('dispose_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->nullable()->constrained('inventories');
            $table->foreignId('po_detail_id')->nullable()->constrained('purchase_order_details');
            $table->foreignId('item_id')->nullable()->constrained('items');
//            $table->unsignedInteger('quantity')->nullable();
//            $table->unsignedInteger('initial_quantity')->nullable();
//            $table->date('purchase_date')->nullable();
            $table->unsignedTinyInteger('inventory_type')->nullable();
//            $table->foreignId('location_id')->nullable()->constrained();
//            $table->string('gl_code')->nullable();
//            $table->text('description')->nullable();
//            $table->string('serial_no')->nullable();
//            $table->string('inventory_no')->nullable();
//            $table->date('physical_verification_date')->nullable();
//            $table->text('remarks')->nullable();
//            $table->integer('auction_quantity')->default(0);
//            $table->integer('donate_quantity')->default(0);
            $table->integer('dispose_quantity')->default(0);
            $table->unsignedTinyInteger('approval_status')->default(4);
            //$table->integer('idle_items')->default(0);

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispose_items');
    }
};
