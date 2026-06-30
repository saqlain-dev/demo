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
        Schema::create('item_variant_logs', function (Blueprint $table) {
            $table->id();
            $table->string('serial_no')->nullable(); 
            $table->foreignId('item_variant_id')->constrained('item_variants');
            $table->foreignId('item_id')->nullable()->constrained('items');
            $table->foreignId('inventory_id')->nullable()->constrained('inventories');
            $table->foreignId('location_id')->nullable()->constrained('locations');
            $table->string('store_id')->nullable();
            $table->date('purchase_date')->nullable();
            $table->foreignId('assign_to_emp')->nullable()->constrained('employees');
            $table->foreignId('assign_to_dept')->nullable()->constrained('type_values');
            $table->unsignedTinyInteger('inventory_type')->nullable()->default(0);
            $table->string('action');
            $table->json('changes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_variant_logs');
    }
};
